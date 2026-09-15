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

use Psr\Cache\CacheItemPoolInterface;

/**
 * Liefert die öffentlichen Trainerlisten unmittelbar aus dem LiMS des DOSB.
 *
 * Anders als das ältere Modul „Lizenzenliste“ liest diese Klasse nicht aus
 * den Tabellen der Lizenzverwaltung, denn die werden nicht mehr gepflegt.
 * Stattdessen holt sie alle gültigen Lizenzen über `/lookup` und die Namen der
 * Verbände über `/lookup_organisations`.
 *
 * Abgerufen wird **einmal für alle Listen**: Die Schnittstelle kennt keinen
 * Filter nach Ausbildungsgang, und vier getrennte Vollabrufe würden die
 * Wartezeit vervierfachen. Das Ergebnis liegt eine Woche im Cache. Schlägt ein
 * Abruf fehl, wird die letzte erfolgreiche Fassung weiter ausgeliefert — eine
 * leere Trainerliste auf der Verbandsseite wäre schlimmer als eine veraltete.
 */
class LimsTrainerliste
{
	/**
	 * Wie lange ein Abruf gültig bleibt, in Sekunden (eine Woche).
	 */
	public const CACHE_DAUER = 604800;

	/**
	 * Cache-Schlüssel des aktuellen Abrufs; läuft nach CACHE_DAUER ab.
	 */
	private const CACHE_AKTUELL = 'lizenzverwaltung_lims_trainerliste';

	/**
	 * Cache-Schlüssel der Reserve; läuft nie ab und wird nur bei Erfolg ersetzt.
	 */
	private const CACHE_RESERVE = 'lizenzverwaltung_lims_trainerliste_reserve';

	/**
	 * Die vier öffentlichen Listen und die Ausbildungsgänge des DOSB, die dazugehören.
	 *
	 * Leistungs- und Breitensport stehen jeweils in einer gemeinsamen Liste, so
	 * wie auf schachbund.de bisher auch. Die Kennungen entsprechen denen, mit
	 * denen die Lizenzverwaltung selbst Lizenzen anlegt (LimsClient::AUSBILDUNGSGANG).
	 *
	 * @var array<string,array{titel:string,kurse:array<int,int>}>
	 */
	public const ARTEN = array
	(
		'A'  => array('titel' => 'A-Trainer', 'kurse' => array(515, 71011)),
		'B'  => array('titel' => 'B-Trainer', 'kurse' => array(514, 71010)),
		'C'  => array('titel' => 'C-Trainer', 'kurse' => array(513, 512)),
		'AB' => array('titel' => 'DOSB-Ausbilder', 'kurse' => array(49337)),
	);

	/**
	 * Höchstzahl an Lizenzen je Abruf; mehr lässt `/lookup` nicht zu.
	 */
	private const SEITE_LIZENZEN = 1000;

	/**
	 * Höchstzahl an Organisationen je Abruf; mehr lässt `/lookup_organisations` nicht zu.
	 */
	private const SEITE_ORGANISATIONEN = 100;

	/**
	 * Obergrenze an Seiten je Abruf, als Schutz gegen eine Schnittstelle, die
	 * `total` falsch meldet und sonst endlos weiterblättern ließe.
	 */
	private const MAX_SEITEN = 50;

	/**
	 * Erzeugt den Dienst.
	 *
	 * @param LimsClient             $client Führt die eigentlichen Aufrufe aus
	 * @param CacheItemPoolInterface $cache  Contaos Anwendungs-Cache (`cache.app`)
	 */
	public function __construct(
		private readonly LimsClient $client,
		private readonly CacheItemPoolInterface $cache,
	) {
	}

	/**
	 * Liefert eine Trainerliste.
	 *
	 * @param string $art  Einer der Schlüssel aus ARTEN: A, B, C oder AB
	 * @param bool   $frisch Umgeht den Cache und ruft sofort neu ab
	 *
	 * @return array{stand:int,eintraege:array<int,array{nachname:string,vorname:string,verband:string,gueltig_bis:int}>,veraltet:bool}
	 *         `stand` ist der Zeitpunkt des zugrunde liegenden Abrufs, 0 wenn
	 *         noch nie einer gelang. `veraltet` ist true, wenn der jüngste
	 *         Abruf fehlschlug und die Reserve ausgeliefert wird. Bei
	 *         unbekannter Art kommt eine leere Liste zurück.
	 */
	public function getListe(string $art, bool $frisch = false): array
	{
		$daten = $this->getDaten($frisch);

		$kurse = self::ARTEN[$art]['kurse'] ?? array();

		$eintraege = array_values(array_filter(
			$daten['lizenzen'],
			static fn (array $l): bool => \in_array($l['kurs'], $kurse, true)
		));

		// Nach Nachname und Vorname, wie auf der bisherigen Seite; Umlaute
		// sortieren über den Vergleich ohne Beachtung der Groß-/Kleinschreibung
		usort($eintraege, static fn (array $a, array $b): int => array($a['nachname_sort'], $a['vorname_sort']) <=> array($b['nachname_sort'], $b['vorname_sort']));

		return array
		(
			'stand'     => $daten['stand'],
			'veraltet'  => $daten['veraltet'],
			'eintraege' => array_map(static fn (array $l): array => array
			(
				'nachname'    => $l['nachname'],
				'vorname'     => $l['vorname'],
				'verband'     => $l['verband'],
				'gueltig_bis' => $l['gueltig_bis'],
			), $eintraege),
		);
	}

	/**
	 * Liefert den zwischengespeicherten Gesamtabruf oder ruft neu ab.
	 *
	 * @param bool $frisch Umgeht den Cache
	 *
	 * @return array{stand:int,lizenzen:array<int,array<string,mixed>>,veraltet:bool}
	 *
	 * Seiteneffekt: Ein erfolgreicher Abruf ersetzt sowohl den aktuellen Eintrag
	 * als auch die Reserve im Cache.
	 */
	private function getDaten(bool $frisch): array
	{
		$aktuell = $this->cache->getItem(self::CACHE_AKTUELL);

		if (!$frisch && $aktuell->isHit())
		{
			return $aktuell->get() + array('veraltet' => false);
		}

		try
		{
			$daten = array('stand' => time(), 'lizenzen' => $this->abrufen());
		}
		catch (\RuntimeException $e)
		{
			Helper::log('Trainerliste aus dem LiMS nicht abrufbar: '.$e->getMessage());

			$reserve = $this->cache->getItem(self::CACHE_RESERVE);

			if ($reserve->isHit())
			{
				return $reserve->get() + array('veraltet' => true);
			}

			return array('stand' => 0, 'lizenzen' => array(), 'veraltet' => true);
		}

		$aktuell->set($daten)->expiresAfter(self::CACHE_DAUER);
		$this->cache->save($aktuell);

		$reserve = $this->cache->getItem(self::CACHE_RESERVE);
		$reserve->set($daten)->expiresAfter(null);
		$this->cache->save($reserve);

		return $daten + array('veraltet' => false);
	}

	/**
	 * Ruft alle gültigen Lizenzen ab und bereitet sie auf.
	 *
	 * Lizenzen ohne Nachnamen werden übergangen: Das LiMS liefert Lizenzen von
	 * Personen, die einer Veröffentlichung nicht zugestimmt haben, anonymisiert
	 * aus, und die gehören nicht auf eine öffentliche Liste.
	 *
	 * @return array<int,array<string,mixed>> Die Lizenzen mit den Schlüsseln
	 *         nachname, vorname, verband, gueltig_bis, kurs sowie den
	 *         Sortierschlüsseln nachname_sort und vorname_sort
	 *
	 * @throws \RuntimeException Wenn die Schnittstelle nicht mit HTTP 200 antwortet
	 *                           oder keine auswertbare Lizenzliste liefert
	 */
	public function abrufen(): array
	{
		$verbaende = $this->abrufenVerbaende();
		$lizenzen  = array();

		// Ohne organisation_id liefert /lookup die Lizenzen des gesamten DOSB
		// (am 2026-09-15 über 520.000, fast alle aus anderen Sportarten).
		// Deshalb je Organisation des Schachbaums einzeln abfragen.
		foreach (array_keys($verbaende) as $orgId)
		{
			$offset = 0;

			for ($seite = 0; $seite < self::MAX_SEITEN; ++$seite)
			{
				$antwort = $this->post('lookup', array
				(
					'organisation_id'   => $orgId,
					'validation_status' => 1,
					'is_obscured'       => 'nein',
					'offset'            => $offset,
					'limit'             => self::SEITE_LIZENZEN,
				));

				$liste = self::findeListe($antwort, array('licenses', 'licences', 'lizenzen', 'items', 'data', 'result', 'results'));

				foreach ($liste as $roh)
				{
					$lizenz = $this->normalisieren($roh, $verbaende);

					if (null === $lizenz)
					{
						continue;
					}

					// Liefert eine übergeordnete Organisation die Lizenzen ihrer
					// Untergliederungen mit, käme dieselbe Lizenz mehrfach an
					$schluessel = $lizenz['schluessel'] ?: 'ohne-'.\count($lizenzen);
					$lizenzen[$schluessel] = $lizenz;
				}

				$offset += \count($liste);

				if (!$liste || $offset >= (int) ($antwort['total'] ?? 0))
				{
					break;
				}
			}
		}

		return array_values($lizenzen);
	}

	/**
	 * Bringt eine einzelne Lizenz aus der Schnittstelle in die Form der Liste.
	 *
	 * Die Schnittstellenbeschreibung nennt die Feldnamen, zeigt aber kein
	 * Antwortbeispiel für `/lookup`. Deshalb werden für Verbandsangaben mehrere
	 * Schreibweisen versucht. Der Verbandsname kommt bevorzugt aus der
	 * Organisationsliste; fehlt er dort, greift `custom_1` — in dieses Feld
	 * schreibt die Lizenzverwaltung beim Übertragen seit jeher den Landesverband.
	 *
	 * @param mixed                 $roh       Ein Eintrag der Lizenzliste
	 * @param array<string,string>  $verbaende Zuordnung Organisations-ID => Name
	 *
	 * @return array<string,mixed>|null Die aufbereitete Lizenz, oder null bei
	 *         anonymisierten oder unbrauchbaren Einträgen
	 */
	private function normalisieren($roh, array $verbaende): ?array
	{
		if (!\is_array($roh))
		{
			return null;
		}

		$nachname = trim((string) ($roh['lastname'] ?? ''));

		if ('' === $nachname)
		{
			return null;
		}

		$vorname = trim((string) ($roh['firstname'] ?? ''));
		$orgId   = (string) ($roh['organisation_id'] ?? $roh['organisation']['id'] ?? '');
		$verband = $verbaende[$orgId]
			?? (string) ($roh['organisation_name'] ?? $roh['organisation']['title'] ?? $roh['organisation']['name'] ?? '');

		if ('' === $verband)
		{
			$verband = trim((string) ($roh['custom_1'] ?? ''));
		}

		return array
		(
			'schluessel'    => (string) ($roh['license_number_dosb'] ?? $roh['lid'] ?? ''),
			'nachname'      => $nachname,
			'vorname'       => $vorname,
			'verband'       => $verband,
			'gueltig_bis'   => (int) ($roh['valid_until'] ?? 0),
			'kurs'          => (int) ($roh['training_course_id'] ?? 0),
			'nachname_sort' => self::sortierschluessel($nachname),
			'vorname_sort'  => self::sortierschluessel($vorname),
		);
	}

	/**
	 * Bildet den Sortierschlüssel eines Namens nach deutscher Telefonbuchordnung.
	 *
	 * Ein schlichter Zeichenkettenvergleich sortiert Umlaute hinter „z“, weil
	 * ihr UTF-8-Byte größer ist — „Ätzel“ stünde hinter „Zeller“. Umlaute werden
	 * deshalb wie ihr Grundbuchstabe behandelt, ß wie ss. Die intl-Erweiterung
	 * (Collator) wird bewusst nicht vorausgesetzt, sie fehlt auf manchen
	 * Servern.
	 *
	 * @param string $name Vor- oder Nachname
	 *
	 * @return string Der Schlüssel in Kleinbuchstaben ohne Umlaute
	 */
	private static function sortierschluessel(string $name): string
	{
		return strtr(mb_strtolower($name), array('ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss', 'é' => 'e', 'è' => 'e', 'á' => 'a', 'ó' => 'o', 'ç' => 'c', 'ñ' => 'n'));
	}

	/**
	 * Ruft die Namen des DSB und aller seiner Untergliederungen ab.
	 *
	 * `/lookup_organisations` liefert mit `organisation_parent_id` nur die
	 * unmittelbar darunterliegende Ebene, deshalb wird Ebene für Ebene
	 * abgestiegen. Schlägt der Abruf fehl, bleibt die Zuordnung leer und die
	 * Liste fällt auf `custom_1` zurück — ein fehlender Verbandsname ist kein
	 * Grund, die ganze Trainerliste zu verwerfen.
	 *
	 * @return array<string,string> Zuordnung Organisations-ID => Name
	 */
	private function abrufenVerbaende(): array
	{
		$namen   = array((string) LimsClient::ORGANISATION_DSB => 'Deutscher Schachbund');
		$offen   = array(LimsClient::ORGANISATION_DSB);
		$gesehen = array();

		try
		{
			while ($offen && \count($gesehen) < 500)
			{
				$eltern = array_shift($offen);

				if (isset($gesehen[$eltern]))
				{
					continue;
				}

				$gesehen[$eltern] = true;
				$offset = 0;

				for ($seite = 0; $seite < self::MAX_SEITEN; ++$seite)
				{
					$antwort = $this->post('lookup_organisations', array
					(
						'organisation_parent_id' => $eltern,
						'offset'                 => $offset,
						'limit'                  => self::SEITE_ORGANISATIONEN,
					));

					$liste = self::findeListe($antwort, array('organisations', 'organisationen', 'items', 'data', 'result', 'results'));

					foreach ($liste as $org)
					{
						$id   = (string) ($org['id'] ?? $org['organisation_id'] ?? '');
						$name = (string) ($org['title'] ?? $org['name'] ?? $org['organisation_name'] ?? '');

						if ('' !== $id && '' !== $name)
						{
							$namen[$id] = $name;
							$offen[]    = (int) $id;
						}
					}

					$offset += \count($liste);

					if (!$liste || $offset >= (int) ($antwort['total'] ?? 0))
					{
						break;
					}
				}
			}
		}
		catch (\RuntimeException $e)
		{
			Helper::log('Verbandsnamen aus dem LiMS nicht abrufbar: '.$e->getMessage());
		}

		return $namen;
	}

	/**
	 * Führt einen POST-Aufruf aus und liefert die entschlüsselte Antwort.
	 *
	 * @param string              $methode Pfad hinter der Basisadresse, etwa "lookup"
	 * @param array<string,mixed> $daten   Die Formularfelder
	 *
	 * @return array<mixed> Die JSON-Antwort als Array
	 *
	 * @throws \RuntimeException Bei Verbindungsfehler, HTTP-Code außer 200 oder
	 *                           einer Antwort, die kein JSON-Objekt ist
	 */
	private function post(string $methode, array $daten): array
	{
		$result = $this->client->request($methode, $daten, 60);

		if (null !== $result['error'])
		{
			throw new \RuntimeException($methode.': '.$result['error']);
		}

		if (200 !== $result['code'])
		{
			throw new \RuntimeException($methode.': HTTP '.$result['code'].' '.substr(trim($result['body']), 0, 200));
		}

		$json = json_decode($result['body'], true);

		if (!\is_array($json))
		{
			throw new \RuntimeException($methode.': Antwort ist kein JSON ('.substr(trim($result['body']), 0, 200).')');
		}

		return $json;
	}

	/**
	 * Sucht in einer Schnittstellenantwort die eigentliche Ergebnisliste.
	 *
	 * Bekannt ist nur, dass `/lookup` die Felder size, offset und total neben
	 * der Liste zurückgibt — nicht, wie der Schlüssel der Liste heißt. Geprüft
	 * werden erst die üblichen Namen, dann jeder Wert, der eine Liste von
	 * Objekten ist. Ist die Antwort selbst eine Liste, wird sie genommen.
	 *
	 * @param array<mixed>      $antwort  Die entschlüsselte Antwort
	 * @param array<int,string> $schluessel Zu bevorzugende Schlüsselnamen
	 *
	 * @return array<int,mixed> Die Einträge; leer, wenn sich keine Liste findet
	 */
	public static function findeListe(array $antwort, array $schluessel): array
	{
		if (array_is_list($antwort))
		{
			return $antwort;
		}

		foreach ($schluessel as $name)
		{
			if (isset($antwort[$name]) && \is_array($antwort[$name]))
			{
				return array_values($antwort[$name]);
			}
		}

		foreach ($antwort as $wert)
		{
			if (\is_array($wert) && $wert && \is_array(reset($wert)))
			{
				return array_values($wert);
			}
		}

		return array();
	}
}
