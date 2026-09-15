<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2016 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Email;
use Contao\FilesModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Verschickt die Lizenzlisten quartalsweise an die Referenten.
 *
 * Bis Fassung 4.3.6 lag diese Aufgabe als `Trainerliste.php` unter
 * `Resources/public/` und wurde per Cron über ihre öffentliche Adresse
 * aufgerufen. Das ging unter Contao 5 nicht mehr — dort fehlt
 * `system/initialize.php` — und war zudem unbedacht: Die Adresse war für jeden
 * erreichbar, ein einziger Aufruf löste den Versand an alle Referenten aus.
 *
 * Der Cron-Eintrag ist deshalb umzustellen auf:
 *
 *     vendor/bin/contao-console lizenzverwaltung:trainerliste
 */
class TrainerlisteCommand extends Command
{
	/**
	 * Standardname des Befehls.
	 *
	 * Als Eigenschaft statt als Attribut, weil `#[AsCommand]` in den von
	 * Contao 4.13 mitgebrachten Symfony-Fassungen noch nicht überall greift.
	 *
	 * @var string
	 */
	protected static $defaultName = 'lizenzverwaltung:trainerliste';

	/**
	 * Spaltenüberschriften der Exportdatei, Spalten A bis X.
	 *
	 * @var array<int,string>
	 */
	private const KOPFZEILE = array
	(
		'Nachname', 'Vorname', 'Titel', 'Geburtsdatum', 'Geschlecht', 'PLZ', 'Ort', 'Straße',
		'Telefon', 'E-Mail', 'Verband', 'DOSB-Lizenz', 'DSB-Lizenz', 'Lizenz-Art', 'Gültig bis',
		'Lizenz-Erwerb', 'Letzte Verlängerung', 'Codex', 'Codex-Datum', 'Erste Hilfe',
		'Erste-Hilfe-Datum', 'Letzte Änderung', 'Veröffentlicht', 'Zeitstempel',
	);

	/**
	 * Erzeugt den Befehl.
	 *
	 * @param ContaoFramework $framework Wird gebraucht, um vor dem Zugriff auf
	 *                                   Contao-Klassen das Framework hochzufahren
	 */
	public function __construct(private readonly ContaoFramework $framework)
	{
		parent::__construct();
	}

	/**
	 * Beschreibt den Befehl und seine Schalter.
	 *
	 * @return void
	 */
	protected function configure(): void
	{
		$this
			->setName('lizenzverwaltung:trainerliste')
			->setDescription('Verschickt die aktuelle Lizenzliste an die Referenten der Landesverbände.')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Verschickt auch dann, wenn im laufenden Quartal bereits versendet wurde.')
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Zeigt nur an, was geschehen würde; verschickt nichts und schreibt nichts.');
	}

	/**
	 * Führt den Versand aus.
	 *
	 * Je Referent, der Informationen wünscht, wird geprüft, ob im laufenden
	 * Quartal schon versendet wurde. Ist das nicht der Fall, wird die
	 * Lizenzliste seines Verbands als Excel-Datei im Versandordner abgelegt und
	 * ihm samt einer Aufstellung der bald ablaufenden Lizenzen zugeschickt.
	 *
	 * @param InputInterface  $input  Die Eingabe samt der Schalter force und dry-run
	 * @param OutputInterface $output Die Ausgabe
	 *
	 * @return int Command::SUCCESS, auch wenn nichts zu versenden war;
	 *             Command::FAILURE, wenn kein Versandordner gewählt ist oder
	 *             mindestens ein Versand fehlschlug. Ein fehlgeschlagener
	 *             Versand bricht den Lauf nicht ab: Die übrigen Referenten
	 *             sollen ihre Liste trotzdem bekommen, und das Versanddatum
	 *             bleibt beim Fehlschlag ungesetzt, sodass der nächste Lauf es
	 *             erneut versucht.
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->framework->initialize();

		$io     = new SymfonyStyle($input, $output);
		$force  = (bool) $input->getOption('force');
		$dryRun = (bool) $input->getOption('dry-run');

		$ordner = $this->getVersandordner();

		if (null === $ordner)
		{
			$io->error('In den Einstellungen ist kein Versandordner gewählt.');

			return Command::FAILURE;
		}

		$verbandsname = Helper::getVerbaende();

		$referenten = Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE sent_info = ? AND published = ? AND email != ?")
		                                     ->execute(1, 1, '');

		if (!$referenten->numRows)
		{
			$io->note('Kein Referent möchte informiert werden.');

			return Command::SUCCESS;
		}

		$io->text($referenten->numRows.' Referent(en) gefunden.');

		$versendet      = 0;
		$fehlgeschlagen = 0;

		while ($referenten->next())
		{
			$name = trim($referenten->vorname.' '.$referenten->nachname);

			if (!$force && !$this->istFaellig((int) $referenten->sent_date))
			{
				$io->text('- '.$name.' ('.$referenten->verband.'): in diesem Quartal bereits versendet, übersprungen.');
				continue;
			}

			$lizenzen = Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.verband = ? AND tl_lizenzverwaltung_items.published = ? AND tl_lizenzverwaltung.published = ? ORDER BY tl_lizenzverwaltung.name, tl_lizenzverwaltung.vorname ASC")
			                                   ->execute($referenten->verband, 1, 1);

			$ablaufend = array();
			$datei     = null;

			if ($lizenzen->numRows)
			{
				$dateiname = 'Lizenzen_'.$referenten->verband.'_'.date('Ymd-Hi').'.xls';
				$datei     = Helper::getProjectDir().'/'.$ordner->path.'/'.$dateiname;

				if (!$dryRun)
				{
					$this->schreibeDatei($lizenzen, $verbandsname, $datei, $ablaufend);
				}
				else
				{
					$this->sammleAblaufende($lizenzen, $ablaufend);
				}
			}

			$io->text('- '.$name.' ('.$referenten->verband.'): '.$lizenzen->numRows.' Lizenz(en), davon '.\count($ablaufend).' bald ablaufend.');

			if ($dryRun)
			{
				continue;
			}

			// Ein nicht erreichbarer Mailserver darf den Lauf nicht abbrechen:
			// Sonst bekämen die noch nicht bearbeiteten Referenten gar nichts,
			// und beim nächsten Lauf stünde derselbe Fehler am selben Empfänger.
			$grund = '';

			try
			{
				$erfolg = $this->verschicke($referenten, $verbandsname, $ablaufend, $datei);
			}
			catch (\Throwable $e)
			{
				$erfolg = false;
				$grund  = ': '.$e->getMessage();
			}

			if ($erfolg)
			{
				Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_referenten SET sent_date = ? WHERE id = ?")
				                       ->execute(time(), $referenten->id);

				++$versendet;
			}
			else
			{
				++$fehlgeschlagen;
				$io->warning('Versand an '.$name.' fehlgeschlagen'.$grund);
			}
		}

		if ($dryRun)
		{
			$io->success('Probelauf beendet.');

			return Command::SUCCESS;
		}

		if ($fehlgeschlagen)
		{
			$io->warning($versendet.' E-Mail(s) versendet, '.$fehlgeschlagen.' fehlgeschlagen.');

			return Command::FAILURE;
		}

		$io->success($versendet.' E-Mail(s) versendet.');

		return Command::SUCCESS;
	}

	/**
	 * Prüft, ob im laufenden Quartal schon versendet wurde.
	 *
	 * @param int $sentDate Zeitpunkt des letzten Versands; 0 wenn noch nie versendet
	 *
	 * @return bool true, wenn versendet werden soll
	 */
	private function istFaellig(int $sentDate): bool
	{
		if (0 === $sentDate)
		{
			return true;
		}

		$quartal = static fn (int $t): string => date('Y', $t).'-'.(int) ceil((int) date('n', $t) / 3);

		return $quartal($sentDate) !== $quartal(time());
	}

	/**
	 * Schreibt die Lizenzliste als Excel-Datei und sammelt die ablaufenden Lizenzen.
	 *
	 * @param object               $lizenzen     Das Ergebnisobjekt der Lizenzabfrage
	 * @param array<string,string> $verbandsname Zuordnung Kennzeichen => Verbandsname
	 * @param string               $datei        Absoluter Pfad der zu schreibenden Datei
	 * @param array<int,array<string,mixed>> $ablaufend Wird um die bald ablaufenden Lizenzen ergänzt
	 *
	 * @return void
	 */
	private function schreibeDatei(object $lizenzen, array $verbandsname, string $datei, array &$ablaufend): void
	{
		$spreadsheet = new Spreadsheet();

		$spreadsheet->getProperties()->setCreator('ContaoLizenzverwaltungBundle')
		            ->setLastModifiedBy('ContaoLizenzverwaltungBundle')
		            ->setTitle('Lizenzen Deutscher Schachbund')
		            ->setSubject('Lizenzen Deutscher Schachbund')
		            ->setDescription('Export der Lizenzen im Deutschen Schachbund')
		            ->setKeywords('export lizenzen dsb schachbund')
		            ->setCategory('Export Lizenzen DSB');

		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Lizenzen');

		foreach (range('A', 'X') as $columnID)
		{
			$sheet->getColumnDimension($columnID)->setAutoSize(true);
		}

		$sheet->getStyle('A1:X1')->applyFromArray(array
		(
			'font'      => array('bold' => true),
			'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER),
			'borders'   => array('bottom' => array('borderStyle' => Border::BORDER_THIN)),
			'fill'      => array
			(
				'fillType'   => Fill::FILL_GRADIENT_LINEAR,
				'rotation'   => 90,
				'startColor' => array('argb' => 'FFA0A0A0'),
				'endColor'   => array('argb' => 'FFFFFFFF'),
			),
		));

		foreach (self::KOPFZEILE as $i => $titel)
		{
			$sheet->setCellValue(array($i + 1, 1), $titel);
		}

		$zeile = 2;

		while ($lizenzen->next())
		{
			$verlaengerung = Helper::getVerlaengerung($lizenzen->erwerb, $lizenzen->verlaengerungen);

			$werte = array
			(
				$lizenzen->name,
				$lizenzen->vorname,
				$lizenzen->titel,
				$this->getDate($lizenzen->geburtstag),
				$lizenzen->geschlecht,
				$lizenzen->plz,
				$lizenzen->ort,
				$lizenzen->strasse,
				$lizenzen->telefon,
				$lizenzen->email,
				$verbandsname[$lizenzen->verband] ?? '',
				$lizenzen->license_number_dosb,
				$lizenzen->lizenznummer,
				$lizenzen->lizenz,
				$this->getDate($lizenzen->gueltigkeit),
				$this->getDate($lizenzen->erwerb),
				$this->getDate($verlaengerung),
				$lizenzen->codex,
				$this->getDate($lizenzen->codex_date),
				$lizenzen->help,
				$this->getDate($lizenzen->help_date),
				$this->getDate($lizenzen->letzteAenderung),
				$lizenzen->published,
				$lizenzen->tstamp ? date('d.m.Y H:i:s', (int) $lizenzen->tstamp) : '',
			);

			foreach ($werte as $i => $wert)
			{
				$sheet->setCellValue(array($i + 1, $zeile), $wert);
			}

			++$zeile;

			$this->pruefeAblauf($lizenzen, $ablaufend);
		}

		$sheet->getStyle('A2:X'.max(2, $zeile - 1))->applyFromArray(array
		(
			'alignment' => array('horizontal' => Alignment::HORIZONTAL_LEFT),
		));

		// Kopfzeile beim Blättern stehen lassen
		$sheet->freezePane('A2');

		IOFactory::createWriter($spreadsheet, 'Xls')->save($datei);
	}

	/**
	 * Sammelt die bald ablaufenden Lizenzen, ohne eine Datei zu schreiben.
	 *
	 * Wird nur beim Probelauf benutzt.
	 *
	 * @param object                         $lizenzen  Das Ergebnisobjekt der Lizenzabfrage
	 * @param array<int,array<string,mixed>> $ablaufend Wird ergänzt
	 *
	 * @return void
	 */
	private function sammleAblaufende(object $lizenzen, array &$ablaufend): void
	{
		while ($lizenzen->next())
		{
			$this->pruefeAblauf($lizenzen, $ablaufend);
		}
	}

	/**
	 * Vermerkt eine Lizenz, wenn sie innerhalb der nächsten sechs Monate abläuft.
	 *
	 * @param object                         $lizenz    Der aktuelle Datensatz
	 * @param array<int,array<string,mixed>> $ablaufend Wird ergänzt
	 *
	 * @return void
	 */
	private function pruefeAblauf(object $lizenz, array &$ablaufend): void
	{
		if ($lizenz->gueltigkeit > time() && strtotime('+6 month') > $lizenz->gueltigkeit)
		{
			$ablaufend[] = array
			(
				'gueltigkeit' => (int) $lizenz->gueltigkeit,
				'name'        => $lizenz->name,
				'vorname'     => $lizenz->vorname,
				'lizenz'      => $lizenz->lizenz,
			);
		}
	}

	/**
	 * Verschickt die Lizenzliste an einen Referenten.
	 *
	 * Die DSB-Referenten bekommen eine Blindkopie. Sie werden je Empfänger neu
	 * gelesen: Früher lief eine einzige Ergebnismenge durch die Schleife über
	 * alle Referenten und war nach dem ersten Empfänger erschöpft — ab dem
	 * zweiten ging deshalb keine Blindkopie mehr heraus.
	 *
	 * @param object                         $referent     Der Empfänger
	 * @param array<string,string>           $verbandsname Zuordnung Kennzeichen => Verbandsname
	 * @param array<int,array<string,mixed>> $ablaufend    Die bald ablaufenden Lizenzen
	 * @param string|null                    $datei        Absoluter Pfad der Anlage, oder null
	 *
	 * @return bool true, wenn die E-Mail angenommen wurde
	 */
	private function verschicke(object $referent, array $verbandsname, array $ablaufend, ?string $datei): bool
	{
		$content  = '<p>Hallo '.$referent->vorname.' '.$referent->nachname.',</p>';
		$content .= '<p>Im Anhang finden Sie die aktuelle Lizenzenliste Ihres Landesverbandes.</p>';

		if ($ablaufend)
		{
			$ablaufend = Helper::sortArrayByFields($ablaufend, array
			(
				'gueltigkeit' => SORT_DESC,
				'name'        => SORT_ASC,
				'vorname'     => SORT_ASC,
				'lizenz'      => SORT_ASC,
			));

			$content .= '<p>Folgende Lizenzen laufen innerhalb der nächsten 6 Monate ab:</p><ul>';

			foreach ($ablaufend as $item)
			{
				$content .= '<li>'.date('d.m.Y', $item['gueltigkeit']).' <b>'.$item['lizenz'].'</b> '.$item['name'].', '.$item['vorname'].'</li>';
			}

			$content .= '</ul>';
		}

		$content .= '<p>Deutscher Schachbund<br>Lizenzverwaltung<br><a href="mailto:lizenzen@schachbund.de">lizenzen@schachbund.de</a></p>';
		$content .= '<p><i>DIESE E-MAIL WURDE AUTOMATISCH GENERIERT!</i></p>';

		$objEmail           = new Email();
		$objEmail->logFile  = 'lizenzverwaltung_email.log';
		$objEmail->from     = 'lizenzen@schachbund.de';
		$objEmail->fromName = 'Deutscher Schachbund';
		$objEmail->subject  = '[DSB-Lizenzen] Aktuelle Liste der Lizenzen '.($verbandsname[$referent->verband] ?? $referent->verband);
		$objEmail->html     = $content;

		if (null !== $datei && file_exists($datei))
		{
			$objEmail->attachFile($datei);
		}

		$bcc = $this->getDsbReferenten();

		if ($bcc)
		{
			$objEmail->sendBcc($bcc);
		}

		return (bool) $objEmail->sendTo(array(trim($referent->vorname.' '.$referent->nachname).' <'.$referent->email.'>'));
	}

	/**
	 * Liest die Adressen der DSB-Referenten, die informiert werden möchten.
	 *
	 * @return array<int,string> Die Adressen in der Form "Name <adresse>"
	 */
	private function getDsbReferenten(): array
	{
		$result = Database::getInstance()->prepare("SELECT vorname, nachname, email FROM tl_lizenzverwaltung_referenten WHERE sent_info = ? AND verband = ? AND published = ? AND email != ?")
		                                 ->execute(1, 'S', 1, '');

		$bcc = array();

		while ($result->next())
		{
			$bcc[] = trim($result->vorname.' '.$result->nachname).' <'.$result->email.'>';
		}

		return $bcc;
	}

	/**
	 * Liefert den in den Einstellungen gewählten Versandordner.
	 *
	 * @return FilesModel|null Das Ordner-Modell, oder null wenn nichts gewählt
	 *                         ist oder der Ordner nicht mehr existiert
	 */
	private function getVersandordner(): ?FilesModel
	{
		$uuid = $GLOBALS['TL_CONFIG']['lizenzverwaltung_versandordner'] ?? '';

		return $uuid ? FilesModel::findByUuid($uuid) : null;
	}

	/**
	 * Wandelt einen Zeitstempel aus der Datenbank in ein lesbares Datum.
	 *
	 * @param mixed $varValue Zeitstempel als Zahl oder Zeichenkette
	 *
	 * @return string Das Datum als TT.MM.JJJJ, oder eine leere Zeichenkette bei leerem Wert
	 */
	private function getDate($varValue): string
	{
		return trim((string) $varValue) ? date('d.m.Y', (int) $varValue) : '';
	}
}
