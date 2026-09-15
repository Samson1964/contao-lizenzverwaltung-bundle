<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

use Contao\Backend;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export der Lizenzen als Excel-Datei.
 *
 * Die Klasse bedient den Schlüssel "exportXLS" des Backend-Moduls. Exportiert
 * wird genau das, was in der Übersicht gerade zu sehen ist: Suche, Filter und
 * der Spezialfilter der Lizenzverwaltung werden aus dem Sitzungsspeicher des
 * Backends übernommen.
 */
class TrainerlizenzExport extends Backend
{
	/**
	 * Spaltenüberschriften der Exportdatei in der Reihenfolge der Spalten A bis X.
	 *
	 * @var array<int,string>
	 */
	private const KOPFZEILE = array
	(
		'Name', 'Vorname', 'Titel', 'Geburtsdatum', 'Geschlecht', 'PLZ', 'Ort', 'Straße',
		'E-Mail', 'Verband', 'DOSB-Lizenz', 'DSB-Lizenz', 'Lizenz-Art', 'Gültig bis',
		'Lizenz-Erwerb', 'Letzte Verlängerung', 'Codex', 'Codex-Datum', 'Erste Hilfe',
		'Erste-Hilfe-Datum', 'Letzte Änderung', 'Bemerkung', 'Veröffentlicht', 'Zeitstempel',
	);

	/**
	 * Feldnamen der Datensätze in der Reihenfolge der Kopfzeile.
	 *
	 * @var array<int,string>
	 */
	private const SPALTEN = array
	(
		'name', 'vorname', 'titel', 'geburtstag', 'geschlecht', 'plz', 'ort', 'strasse',
		'email', 'verband', 'lizenznummer_dosb', 'lizenznummer', 'lizenz', 'gueltigkeit',
		'erwerb', 'verlaengerungen', 'codex', 'codex_date', 'help', 'help_date',
		'letzteAenderung', 'bemerkung', 'published', 'tstamp',
	);

	/**
	 * Erzeugt das Objekt.
	 *
	 * Der öffentliche Konstruktor ist Pflicht: Unter Contao 4.13 ist
	 * `Backend::__construct()` nur protected.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Erzeugt die Excel-Datei und schickt sie an den Browser.
	 *
	 * @param DataContainer $dc Der Data Container der Lizenzübersicht; daraus
	 *                          werden Tabellenname und Filterzustand gelesen
	 *
	 * @return string Leere Zeichenkette, wenn ein anderer Schlüssel anliegt.
	 *                Sonst wird die Datei ausgeliefert und die Ausführung
	 *                beendet — die Methode kehrt dann nicht zurück.
	 */
	public function exportTrainer_XLS(DataContainer $dc): string
	{
		if (Input::get('key') !== 'exportXLS')
		{
			return '';
		}

		$arrExport = $this->getRecords($dc);

		$spreadsheet = new Spreadsheet();

		$spreadsheet->getProperties()->setCreator('ContaoLizenzverwaltungBundle')
		            ->setLastModifiedBy('ContaoLizenzverwaltungBundle')
		            ->setTitle('Lizenzen Deutscher Schachbund')
		            ->setSubject('Lizenzen Deutscher Schachbund')
		            ->setDescription('Export der Lizenzen im Deutschen Schachbund')
		            ->setKeywords('export lizenzen dsb schachbund')
		            ->setCategory('Export Lizenzen DSB');

		$styleKopf = array
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
		);

		$styleDaten = array
		(
			'alignment' => array('horizontal' => Alignment::HORIZONTAL_LEFT),
		);

		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Lizenzen');

		foreach (range('A', 'X') as $columnID)
		{
			$sheet->getColumnDimension($columnID)->setAutoSize(true);
		}

		// Die Datenformatierung reicht bis zur letzten belegten Zeile, mindestens aber bis Zeile 2
		$letzteZeile = max(2, \count($arrExport) + 1);

		$sheet->getStyle('A1:X1')->applyFromArray($styleKopf);
		$sheet->getStyle('A2:X'.$letzteZeile)->applyFromArray($styleDaten);

		foreach (self::KOPFZEILE as $i => $titel)
		{
			$sheet->setCellValue(array($i + 1, 1), $titel);
		}

		$zeile = 2;

		foreach ($arrExport as $item)
		{
			foreach (self::SPALTEN as $i => $feld)
			{
				$sheet->setCellValue(array($i + 1, $zeile), $item[$feld]);
			}

			++$zeile;
		}

		// Kopfzeile beim Blättern stehen lassen
		$sheet->freezePane('A2');

		$spreadsheet->setActiveSheetIndex(0);

		$dateiname = 'Lizenzen_'.date('Ymd-Hi').'.xls';

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateiname.'"');
		header('Cache-Control: max-age=0');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Pragma: public');

		IOFactory::createWriter($spreadsheet, 'Xls')->save('php://output');

		// Ohne den harten Abbruch würde Contao seine Backend-Seite an die
		// Excel-Datei anhängen und sie damit unlesbar machen
		exit;
	}

	/**
	 * Liest die zu exportierenden Datensätze aus der Datenbank.
	 *
	 * Suche, Standardfilter und der Spezialfilter der Lizenzverwaltung stehen
	 * im Sitzungsspeicher des Backends und werden hier in eine Bedingung
	 * übersetzt. Werte gehen dabei als gebundene Parameter in die Abfrage; das
	 * Suchfeld und die Filterfelder werden gegen die im DCA definierten
	 * Feldnamen geprüft, weil ein Spaltenname sich nicht binden lässt.
	 *
	 * @param DataContainer $dc Der Data Container der Lizenzübersicht
	 *
	 * @return array<int,array<string,mixed>> Die aufbereiteten Datensätze; leer,
	 *                                        wenn nichts auf die Bedingung passt
	 */
	public function getRecords(DataContainer $dc): array
	{
		$table   = (string) $dc->table;
		$session = Helper::getBackendSessionBag();

		$bedingungen = array();
		$parameter   = array();

		if (null !== $session)
		{
			$this->applySearch($session->get('search')[$table] ?? null, $bedingungen, $parameter);
			$this->applyFilter($session->get('filter')[$table] ?? null, $table, $bedingungen, $parameter);
			$this->applySpecialFilter($session->get('filter')[$table.'Filter']['tli_filter'] ?? null, $bedingungen, $parameter);
		}

		$bedingungen[] = "tl_lizenzverwaltung_items.published = '1'";
		$bedingungen[] = "tl_lizenzverwaltung.published = '1'";

		$sql = "SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE "
		     . implode(' AND ', $bedingungen)
		     . " ORDER BY name, vorname ASC";

		Helper::log('Excel-Export mit: '.$sql);

		$records = Database::getInstance()->prepare($sql)->execute(...$parameter);

		$verbandsname = Helper::getVerbaende();

		$arrExport = array();

		while ($records->next())
		{
			$arrExport[] = array
			(
				'vorname'           => $records->vorname,
				'name'              => $records->name,
				'lizenznummer_dosb' => $records->license_number_dosb,
				'lizenznummer'      => $records->lizenznummer,
				'lizenz'            => $records->lizenz,
				'gueltigkeit'       => $this->getDate($records->gueltigkeit),
				'geburtstag'        => $this->getDate($records->geburtstag),
				'geschlecht'        => $records->geschlecht,
				'strasse'           => $records->strasse,
				'plz'               => $records->plz,
				'ort'               => $records->ort,
				'erwerb'            => $this->getDate($records->erwerb),
				'verlaengerungen'   => $this->getDate(Helper::getVerlaengerung($records->erwerb, $records->verlaengerungen)),
				'codex'             => $records->codex,
				'codex_date'        => $this->getDate($records->codex_date),
				'help'              => $records->help,
				'help_date'         => $this->getDate($records->help_date),
				'letzteAenderung'   => $this->getDate($records->letzteAenderung),
				'bemerkung'         => strip_tags((string) $records->bemerkung),
				'published'         => $records->published,
				'titel'             => $records->titel,
				'email'             => $records->email,
				'verband'           => $verbandsname[$records->verband] ?? '',
				'tstamp'            => $records->tstamp ? date('d.m.Y H:i:s', (int) $records->tstamp) : '',
			);
		}

		return $arrExport;
	}

	/**
	 * Übernimmt den Suchbegriff der Übersicht in die Abfrage.
	 *
	 * Contao sucht mit REGEXP über den als Text gelesenen Spaltenwert; das wird
	 * hier nachgebildet, damit der Export dieselbe Treffermenge liefert wie die
	 * Liste.
	 *
	 * @param array<string,mixed>|null $search      Der Eintrag aus dem Sitzungsspeicher
	 *                                              mit den Schlüsseln field und value
	 * @param array<int,string>        $bedingungen Wird um die Bedingung ergänzt
	 * @param array<int,mixed>         $parameter   Wird um den Suchwert ergänzt
	 *
	 * @return void Ist kein oder ein unbekanntes Feld gewählt, passiert nichts
	 */
	private function applySearch(?array $search, array &$bedingungen, array &$parameter): void
	{
		if (empty($search['field']) || !isset($search['value']) || '' === (string) $search['value'])
		{
			return;
		}

		$field = $this->qualifyField((string) $search['field']);

		if (null === $field)
		{
			return;
		}

		$bedingungen[] = "LOWER(CAST($field AS CHAR)) REGEXP LOWER(?)";
		$parameter[]   = $search['value'];
	}

	/**
	 * Übernimmt die Filter der Übersicht in die Abfrage.
	 *
	 * @param array<string,mixed>|null $filter      Der Eintrag aus dem Sitzungsspeicher;
	 *                                              der Schlüssel "limit" wird übergangen,
	 *                                              weil der Export nie seitenweise erfolgt
	 * @param string                   $table       Name der Haupttabelle
	 * @param array<int,string>        $bedingungen Wird um die Bedingungen ergänzt
	 * @param array<int,mixed>         $parameter   Wird um die Filterwerte ergänzt
	 *
	 * @return void Unbekannte Feldnamen werden stillschweigend übergangen
	 */
	private function applyFilter(?array $filter, string $table, array &$bedingungen, array &$parameter): void
	{
		if (!$filter)
		{
			return;
		}

		foreach ($filter as $key => $value)
		{
			if ('limit' === $key)
			{
				continue;
			}

			$field = $this->qualifyField((string) $key, $table);

			if (null === $field)
			{
				continue;
			}

			$bedingungen[] = "$field = ?";
			$parameter[]   = $value;
		}
	}

	/**
	 * Übernimmt den Spezialfilter der Lizenzverwaltung in die Abfrage.
	 *
	 * Die Werte 1 bis 3 filtern nach Gültigkeit und Markierung; ein Wert, der
	 * mit "V" beginnt, filtert nach dem Verband, dessen Kennzeichen an zweiter
	 * Stelle steht.
	 *
	 * @param string|null       $filter      Der gewählte Wert
	 * @param array<int,string> $bedingungen Wird um die Bedingung ergänzt
	 * @param array<int,mixed>  $parameter   Wird um den Wert ergänzt
	 *
	 * @return void Bei unbekanntem Wert passiert nichts
	 */
	private function applySpecialFilter(?string $filter, array &$bedingungen, array &$parameter): void
	{
		if (!$filter)
		{
			return;
		}

		switch ($filter)
		{
			case '1': // Alle Personen mit gültigen Lizenzen
				$bedingungen[] = 'tl_lizenzverwaltung_items.gueltigkeit >= ?';
				$parameter[]   = time();
				break;

			case '2': // Alle Personen mit ungültigen Lizenzen
				$bedingungen[] = 'tl_lizenzverwaltung_items.gueltigkeit < ?';
				$parameter[]   = time();
				break;

			case '3': // Alle Personen mit markierten Lizenzen
				$bedingungen[] = 'tl_lizenzverwaltung_items.marker = ?';
				$parameter[]   = 1;
				break;

			default:
				if ('V' === substr($filter, 0, 1) && \strlen($filter) === 2)
				{
					$bedingungen[] = 'tl_lizenzverwaltung_items.verband = ?';
					$parameter[]   = substr($filter, 1, 1);
				}
		}
	}

	/**
	 * Prüft einen Feldnamen und stellt den Tabellennamen voran.
	 *
	 * Spaltennamen lassen sich nicht als Parameter binden, deshalb muss der
	 * Name gegen die im DCA definierten Felder geprüft werden, bevor er in die
	 * Abfrage geht.
	 *
	 * @param string $field Der zu prüfende Feldname
	 * @param string $table Die Tabelle, in der zuerst gesucht wird
	 *
	 * @return string|null Der qualifizierte Name wie "tl_lizenzverwaltung.name",
	 *                     oder null wenn das Feld in keiner der beiden Tabellen
	 *                     der Abfrage vorkommt
	 */
	private function qualifyField(string $field, string $table = 'tl_lizenzverwaltung'): ?string
	{
		foreach (array($table, 'tl_lizenzverwaltung', 'tl_lizenzverwaltung_items') as $candidate)
		{
			Controller::loadDataContainer($candidate);

			if (isset($GLOBALS['TL_DCA'][$candidate]['fields'][$field]['sql']))
			{
				return $candidate.'.'.$field;
			}
		}

		return null;
	}

	/**
	 * Wandelt einen Zeitstempel aus der Datenbank in ein lesbares Datum.
	 *
	 * @param mixed $varValue Zeitstempel als Zahl oder Zeichenkette
	 *
	 * @return string Das Datum als TT.MM.JJJJ, oder eine leere Zeichenkette bei
	 *                leerem Wert
	 */
	public function getDate($varValue): string
	{
		return trim((string) $varValue) ? date('d.m.Y', (int) $varValue) : '';
	}
}
