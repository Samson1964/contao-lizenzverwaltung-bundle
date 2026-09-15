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

use Codefog\HasteBundle\StringParser;
use Contao\Database;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Sammlung von Hilfsfunktionen der Lizenzverwaltung.
 *
 * Die Klasse erfüllt zwei Aufgaben. Zum einen liefert sie die fachlichen
 * Stammdaten (Verbände, Lizenzarten, Verbandsmails), zum anderen kapselt sie
 * jene Contao-Aufrufe, die sich zwischen Contao 4.13 und Contao 5
 * unterscheiden. Der Rest des Bundles ruft ausschließlich diese Methoden auf
 * und muss die Fassungsunterschiede deshalb nicht kennen.
 *
 * Die Klasse erbt bewusst von keiner Contao-Klasse: Sie wird sowohl im
 * Backend als auch im Frontend benutzt, und ein `Frontend`-Vorfahre würde
 * unter Contao 5 beim Erzeugen eine Deprecation auslösen, ohne dass davon
 * irgendetwas gebraucht wird.
 */
class Helper
{
	/**
	 * Liefert das Wurzelverzeichnis der Contao-Installation.
	 *
	 * Ersetzt die Konstante TL_ROOT, die es unter Contao 5 nicht mehr gibt.
	 * Der Parameter `kernel.project_dir` existiert in beiden Fassungen und
	 * zeigt auf dasselbe Verzeichnis, auf das TL_ROOT unter Contao 4 zeigte.
	 *
	 * @return string Absoluter Pfad ohne abschließenden Schrägstrich
	 */
	public static function getProjectDir(): string
	{
		return (string) System::getContainer()->getParameter('kernel.project_dir');
	}

	/**
	 * Liefert die aktuelle Sitzung.
	 *
	 * Ersatz für `Session::getInstance()` und `$this->Session`, die es unter
	 * Contao 5 beide nicht mehr gibt. Der Dienst `session` fehlt dort
	 * ebenfalls, deshalb der Umweg über den Request-Stapel.
	 *
	 * @return SessionInterface|null Die Sitzung, oder null wenn gar kein
	 *                               Request läuft (etwa auf der Kommandozeile)
	 */
	public static function getSession(): ?SessionInterface
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if (null === $request || !$request->hasSession())
		{
			return null;
		}

		return $request->getSession();
	}

	/**
	 * Liefert den Sitzungsspeicher des Backends.
	 *
	 * In diesem Speicher legt der DC_Table die Zustände der Filterleiste ab:
	 * `filter[<tabelle>]` die Filterwerte, `search[<tabelle>]` das Suchfeld und
	 * den Suchbegriff. Früher kam man über `$dc->Session` heran; unter Contao 5
	 * liefert diese Eigenschaft null, weil `System::__get()` dort nichts mehr
	 * selbsttätig importiert.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface|null
	 *         Der Speicher, oder null wenn keine Sitzung läuft
	 */
	public static function getBackendSessionBag()
	{
		$session = self::getSession();

		if (null === $session)
		{
			return null;
		}

		try
		{
			return $session->getBag('contao_backend');
		}
		catch (\Throwable $e)
		{
			return null;
		}
	}

	/**
	 * Liefert das aktuelle CSRF-Token.
	 *
	 * Ersatz für die unter Contao 5 entfallene Konstante REQUEST_TOKEN. Der
	 * Dienst `contao.csrf.token_manager` ist in beiden Fassungen öffentlich.
	 *
	 * @return string Das Token, im Zweifel eine leere Zeichenkette
	 */
	public static function getRequestToken(): string
	{
		return (string) System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
	}

	/**
	 * Liefert die Adresse des Backend-Einstiegs.
	 *
	 * Früher stand hier eine Fallunterscheidung über die Konstante VERSION
	 * (Contao 3 gegen Contao 4). Die Konstante gibt es unter Contao 5 nicht
	 * mehr, und Contao 3 wird ohnehin nicht mehr unterstützt — bleibt die
	 * Route, die in beiden unterstützten Fassungen gleich heißt.
	 *
	 * @return string Zum Beispiel "/contao"
	 */
	public static function getBackendRoute(): string
	{
		return (string) System::getContainer()->get('router')->generate('contao_backend');
	}

	/**
	 * Schreibt eine Zeile in ein eigenes Protokoll.
	 *
	 * Ersatz für die unter Contao 5 entfallene Funktion `log_message()`. Diese
	 * schrieb in eine Datei unterhalb von `var/logs`; hier wird stattdessen
	 * Contaos Fehlerprotokoll benutzt, das in beiden Fassungen existiert. Der
	 * frühere Dateiname wird als Präfix mitgeführt, damit sich die Einträge im
	 * gemeinsamen Protokoll weiterhin zuordnen lassen.
	 *
	 * @param string $message Der Protokolltext, darf mehrzeilig sein
	 * @param string $context Kurzbezeichnung des Vorgangs, erscheint im Präfix
	 *
	 * @return void Fehler beim Protokollieren werden bewusst verschluckt; ein
	 *              nicht schreibbares Protokoll darf den Lizenzabruf nicht
	 *              abbrechen
	 */
	public static function log(string $message, string $context = 'lizenzverwaltung'): void
	{
		try
		{
			System::getContainer()->get('monolog.logger.contao.error')->info('['.$context.'] '.$message);
		}
		catch (\Throwable $e)
		{
			// Ohne Protokoll weitermachen
		}
	}

	/**
	 * Ersetzt Platzhalter und Insert-Tags in einem Text.
	 *
	 * Bis Haste 4 lag diese Funktion als statische Methode
	 * `\Haste\Util\StringUtil::recursiveReplaceTokensAndTags()` vor. Haste 5
	 * hat sie in den Dienst `Codefog\HasteBundle\StringParser` verschoben; die
	 * alte Klasse existiert nicht mehr. Da Haste 5.4 sowohl Contao 4.13 als
	 * auch Contao 5.3+ bedient, ist der Dienst der einzige Weg, der in beiden
	 * Fassungen funktioniert.
	 *
	 * @param string               $text   Der Text mit Platzhaltern der Form ##name##
	 * @param array<string,mixed>  $tokens Zuordnung Platzhaltername => Ersetzung
	 *
	 * @return string Der ersetzte Text; steht der Dienst nicht bereit, kommt
	 *                der Text unverändert zurück
	 */
	public static function replaceTokens(string $text, array $tokens): string
	{
		$container = System::getContainer();

		// Haste 5: Dienst
		if (class_exists(StringParser::class) && $container->has(StringParser::class))
		{
			return $container->get(StringParser::class)->recursiveReplaceTokensAndTags($text, $tokens);
		}

		// Haste 4: statische Methode. Viele Contao-4.13-Installationen haben
		// Haste 4 fest in ihrer composer.json stehen, weil andere Erweiterungen
		// Haste 5 noch nicht vertragen — deshalb werden beide Fassungen bedient.
		if (class_exists('Haste\Util\StringUtil'))
		{
			return \call_user_func(array('Haste\Util\StringUtil', 'recursiveReplaceTokensAndTags'), $text, $tokens);
		}

		return $text;
	}

	/**
	 * Liefert das Datum der letzten Verlängerung einer Lizenz.
	 *
	 * @param mixed $erwerb          Zeitstempel des Lizenzerwerbs
	 * @param mixed $verlaengerungen Serialisiertes Array der Verlängerungen in
	 *                               aufsteigender Reihenfolge, jeder Eintrag mit
	 *                               dem Schlüssel "datum"
	 *
	 * @return int Zeitstempel der letzten Verlängerung; gibt es keine, wird der
	 *             Erwerbszeitpunkt zurückgegeben
	 */
	public static function getVerlaengerung($erwerb, $verlaengerungen): int
	{
		$return = 0;

		if ($verlaengerungen)
		{
			$temp = @unserialize((string) $verlaengerungen, array('allowed_classes' => false));

			if (\is_array($temp))
			{
				foreach ($temp as $item)
				{
					if (isset($item['datum']))
					{
						$return = (int) $item['datum'];
					}
				}
			}
		}

		return $return ?: (int) $erwerb;
	}

	/**
	 * Liest die veröffentlichten Verbände aus der Datenbank.
	 *
	 * Vor dem Lesen wird geprüft, ob es die Tabelle überhaupt gibt: Beim
	 * allerersten `composer install` ist sie noch nicht angelegt, der DcaLoader
	 * ruft diese Methode aber schon auf, um die Auswahlliste des Feldes
	 * `verband` zu füllen.
	 *
	 * @return array<string,string> Zuordnung Verbandskennzeichen => Verbandsname,
	 *                              leer wenn die Tabelle fehlt oder nichts
	 *                              veröffentlicht ist
	 */
	public static function getVerbaende(): array
	{
		$return = array();

		if (!Database::getInstance()->tableExists('tl_lizenzverwaltung_verbaende'))
		{
			return $return;
		}

		$result = Database::getInstance()->prepare("SELECT kennzeichen, name FROM tl_lizenzverwaltung_verbaende WHERE published = ?")
		                                 ->execute(1);

		while ($result->next())
		{
			$return[$result->kennzeichen] = $result->name;
		}

		return $return;
	}

	/**
	 * Liefert die Organisationsnummern der untergeordneten Verbände.
	 *
	 * @param string $kennzeichen Kennzeichen des übergeordneten Verbands, etwa "3" für Berlin
	 *
	 * @return array<int,string> Liste der Organisationsnummern beim DOSB;
	 *                           leer, wenn der Verband unbekannt ist oder keine
	 *                           Untergliederung hinterlegt hat
	 */
	public static function getUntergliederung(string $kennzeichen): array
	{
		$return = array();

		if (!Database::getInstance()->tableExists('tl_lizenzverwaltung_verbaende'))
		{
			return $return;
		}

		$result = Database::getInstance()->prepare("SELECT organisation FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen=? AND published=?")
		                                 ->limit(1)
		                                 ->execute($kennzeichen, 1);

		if ($result->numRows && $result->organisation)
		{
			$daten = @unserialize((string) $result->organisation, array('allowed_classes' => false));

			if (\is_array($daten))
			{
				foreach ($daten as $item)
				{
					if (isset($item['organisation_id']))
					{
						$return[] = $item['organisation_id'];
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Liefert den Namen eines Verbands zu seinem Kennzeichen.
	 *
	 * @param string|null $verband Das Kennzeichen, etwa "S" für den Deutschen Schachbund
	 *
	 * @return string Der Verbandsname, oder eine leere Zeichenkette bei
	 *                unbekanntem Kennzeichen
	 */
	public static function getVerband(?string $verband): string
	{
		return self::getVerbaende()[(string) $verband] ?? '';
	}

	/**
	 * Liefert die im Deutschen Schachbund vergebenen Lizenzarten.
	 *
	 * Die Liste ist bewusst fest verdrahtet: Sie wird an mehreren Stellen mit
	 * den Ausbildungsgängen des DOSB verknüpft (siehe DOSBLizenzen) und darf
	 * deshalb nicht redaktionell veränderbar sein.
	 *
	 * @return array<string,string> Zuordnung Kürzel => Klartextbezeichnung
	 */
	public static function getLizenzen(): array
	{
		return array
		(
			'A'                   => 'A-Trainer Leistungssport',
			'A-B'                 => 'A-Trainer Breitensport',
			'B'                   => 'B-Trainer Leistungssport',
			'B-B'                 => 'B-Trainer Breitensport',
			'C'                   => 'C-Trainer Leistungssport',
			'C-B'                 => 'C-Trainer Breitensport',
			'C-Sonderlizenz'      => 'C-Sonderlizenz',
			'F'                   => 'F',
			'F/C'                 => 'F/C',
			'J'                   => 'J',
			'AB-Z'                => 'Ausbilder-Zertifikat'
		);
	}

	/**
	 * Sortiert ein mehrdimensionales Array nach mehreren Feldern.
	 *
	 * Aufruf zum Beispiel mit
	 * `sortArrayByFields($daten, array('jahrgang' => SORT_DESC, 'nachname' => SORT_ASC))`.
	 *
	 * Die Methode baut das Array in Spaltenlisten um, weil `array_multisort()`
	 * genau diese Form erwartet: je Sortierkriterium eine flache Werteliste,
	 * gefolgt von der Sortierrichtung, und ganz am Ende das zu sortierende
	 * Array als Referenz.
	 *
	 * @param array<int,array<string,mixed>> $arr    Die zu sortierenden Datensätze
	 * @param array<string,int>              $fields Zuordnung Feldname => SORT_ASC oder SORT_DESC
	 *
	 * @return array<int,array<string,mixed>> Das sortierte Array; ein leeres
	 *                                        Eingabearray kommt unverändert zurück
	 */
	public static function sortArrayByFields(array $arr, array $fields): array
	{
		if (!$arr)
		{
			return $arr;
		}

		$sortFields = array();
		$args       = array();

		foreach ($arr as $key => $row)
		{
			foreach ($fields as $field => $order)
			{
				$sortFields[$field][$key] = $row[$field] ?? null;
			}
		}

		foreach ($fields as $field => $order)
		{
			$args[] = $sortFields[$field];
			$args[] = \is_array($order) ? ($order[0] ?? SORT_ASC) : $order;
		}

		$args[] = &$arr;

		array_multisort(...$args);

		return $arr;
	}

	/**
	 * Liest die E-Mail-Adressen aller veröffentlichten Referenten.
	 *
	 * Das Ergebnis wird innerhalb eines Aufrufs zwischengespeichert, weil die
	 * Liste beim Aufbau der Lizenzübersicht für jede Zeile erneut gebraucht
	 * wird.
	 *
	 * @return array<string,string> Zuordnung Verbandskennzeichen => E-Mail-Adresse
	 */
	public static function getVerbandsmails(): array
	{
		static $verbandsmails = null;

		if (null === $verbandsmails)
		{
			$verbandsmails = array();

			if (Database::getInstance()->tableExists('tl_lizenzverwaltung_referenten'))
			{
				$result = Database::getInstance()->prepare("SELECT verband, email FROM tl_lizenzverwaltung_referenten WHERE published = ?")
				                                 ->execute(1);

				while ($result->next())
				{
					$verbandsmails[$result->verband] = $result->email;
				}
			}
		}

		return $verbandsmails;
	}

	/**
	 * Liefert die E-Mail-Adresse des Referenten eines Verbands.
	 *
	 * @param string|null $verband Verbandskennzeichen
	 *
	 * @return string Die Adresse, oder eine leere Zeichenkette wenn der Verband
	 *                keinen veröffentlichten Referenten hat
	 */
	public static function getVerbandMail(?string $verband): string
	{
		return self::getVerbandsmails()[(string) $verband] ?? '';
	}

	/**
	 * Liefert die E-Mail-Adresse einer Person aus der Lizenzverwaltung.
	 *
	 * @param int|string $id Datensatz-ID in tl_lizenzverwaltung
	 *
	 * @return string Die Adresse, oder eine leere Zeichenkette wenn es den
	 *                Datensatz nicht gibt oder keine Adresse hinterlegt ist
	 */
	public static function getPersonMail($id): string
	{
		$result = Database::getInstance()->prepare("SELECT email FROM tl_lizenzverwaltung WHERE id = ?")
		                                 ->limit(1)
		                                 ->execute($id);

		return $result->numRows ? (string) $result->email : '';
	}
}
