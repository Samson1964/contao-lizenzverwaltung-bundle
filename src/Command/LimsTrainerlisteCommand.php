<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsClient;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsTrainerliste;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Ruft die Trainerlisten aus dem LiMS ab und zeigt, was dabei ankommt.
 *
 * Zwei Aufgaben: Zum einen lässt sich damit der Cache vorab füllen, damit der
 * erste Besucher der Seite nicht auf den Vollabruf warten muss. Zum anderen
 * zeigt `--rohdaten` den Aufbau der Schnittstellenantwort — die
 * Schnittstellenbeschreibung enthält dafür kein Beispiel.
 */
class LimsTrainerlisteCommand extends Command
{
	/**
	 * Standardname des Befehls.
	 *
	 * @var string
	 */
	protected static $defaultName = 'lizenzverwaltung:lims-trainerliste';

	/**
	 * Erzeugt den Befehl.
	 *
	 * @param ContaoFramework  $framework Wird für Config und Protokoll hochgefahren
	 * @param LimsTrainerliste $liste     Der Abrufdienst
	 * @param LimsClient       $client    Für den Rohabruf mit `--rohdaten`
	 */
	public function __construct(
		private readonly ContaoFramework $framework,
		private readonly LimsTrainerliste $liste,
		private readonly LimsClient $client,
	) {
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
			->setName('lizenzverwaltung:lims-trainerliste')
			->setDescription('Ruft die Trainerlisten aus dem LiMS ab und füllt den Cache.')
			->addOption('frisch', 'f', InputOption::VALUE_NONE, 'Umgeht den Cache und ruft neu ab.')
			->addOption('rohdaten', null, InputOption::VALUE_NONE, 'Zeigt den Aufbau der ersten Antworten von /lookup und /lookup_organisations, Namen gekürzt.');
	}

	/**
	 * Führt den Abruf aus und gibt je Liste die Anzahl und die ersten Einträge aus.
	 *
	 * @param InputInterface  $input  Die Eingabe
	 * @param OutputInterface $output Die Ausgabe
	 *
	 * @return int Command::FAILURE, wenn keine einzige Lizenz ankam
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->framework->initialize();

		$io = new SymfonyStyle($input, $output);

		if ($input->getOption('rohdaten'))
		{
			$this->zeigeRohdaten($io);
		}

		$gesamt = 0;

		foreach (LimsTrainerliste::ARTEN as $art => $info)
		{
			$liste = $this->liste->getListe($art, (bool) $input->getOption('frisch') && 0 === $gesamt);

			$gesamt += \count($liste['eintraege']);

			$io->section($info['titel'].': '.\count($liste['eintraege']).' Einträge');

			foreach (\array_slice($liste['eintraege'], 0, 3) as $e)
			{
				$io->text(sprintf('%s, %s — %s — %s', $e['nachname'], $e['vorname'], $e['verband'] ?: '(kein Verband)', $e['gueltig_bis'] ? date('d.m.Y', $e['gueltig_bis']) : '-'));
			}

			if ($liste['veraltet'])
			{
				$io->warning('Abruf fehlgeschlagen, ausgeliefert wird die Reserve vom '.($liste['stand'] ? date('d.m.Y H:i', $liste['stand']) : 'nie').'. Einzelheiten im Contao-Protokoll.');
			}
		}

		if (0 === $gesamt)
		{
			$io->error('Keine Lizenzen erhalten. Mit --rohdaten lässt sich die Antwort der Schnittstelle ansehen.');

			return Command::FAILURE;
		}

		$io->success($gesamt.' Einträge im Cache.');

		return Command::SUCCESS;
	}

	/**
	 * Gibt den Aufbau der ersten Antworten aus.
	 *
	 * Personenbezogene Werte werden auf ihren ersten Buchstaben gekürzt, damit
	 * die Ausgabe gefahrlos weitergegeben werden kann.
	 *
	 * @param SymfonyStyle $io Die Ausgabe
	 *
	 * @return void
	 */
	private function zeigeRohdaten(SymfonyStyle $io): void
	{
		foreach (array('lookup' => array('validation_status' => 1, 'limit' => 2), 'lookup_organisations' => array('organisation_parent_id' => LimsClient::ORGANISATION_DSB, 'limit' => 2)) as $methode => $daten)
		{
			$r = $this->client->request($methode, $daten, 60);

			$io->section('/'.$methode.' → HTTP '.$r['code'].($r['error'] ? ' '.$r['error'] : ''));

			$json = json_decode($r['body'], true);

			$io->text(\is_array($json) ? json_encode(self::kuerzen($json), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : substr($r['body'], 0, 500));
		}
	}

	/**
	 * Kürzt personenbezogene Werte in einer Antwort auf den ersten Buchstaben.
	 *
	 * @param mixed $wert Ein beliebiger Wert der Antwort
	 *
	 * @return mixed Derselbe Aufbau mit gekürzten Werten
	 */
	private static function kuerzen($wert)
	{
		if (!\is_array($wert))
		{
			return $wert;
		}

		foreach ($wert as $k => $v)
		{
			if (\in_array($k, array('firstname', 'lastname', 'birthdate', 'street', 'postal', 'city', 'mail', 'phone', 'academic_title'), true) && \is_scalar($v) && '' !== (string) $v)
			{
				$wert[$k] = mb_substr((string) $v, 0, 1).'…';
			}
			else
			{
				$wert[$k] = self::kuerzen($v);
			}
		}

		return $wert;
	}
}
