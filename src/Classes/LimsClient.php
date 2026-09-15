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

use Contao\Config;
use Contao\Database;

/**
 * Zugriff auf das Lizenzmanagementsystem (LiMS) des DOSB.
 *
 * Die Klasse bündelt die gesamte Kommunikation mit dem LiMS. Bis Fassung 4.3.6
 * lag derselbe curl-Aufruf dreifach im Bundle: in DOSBLizenzen für den
 * Einzelabruf und je einmal in den Skripten ajaxRequest.php und
 * ajaxRequestUmzug.php für den Stapellauf. Die Fassungen liefen dabei
 * auseinander — der Stapellauf übermittelte etwa das Geburtsdatum ohne die
 * Zeitkorrektur, die der Einzelabruf vornimmt. Seit Fassung 5.0.0 gibt es nur
 * noch diesen einen Weg.
 */
class LimsClient
{
	/**
	 * Organisationsnummer des Deutschen Schachbundes beim DOSB.
	 */
	public const ORGANISATION_DSB = 1093;

	/**
	 * Zuordnung der DSB-Lizenzarten zu den Ausbildungsgängen des DOSB.
	 *
	 * Die mit "fiktiv" bezeichneten Arten kennt der DOSB nicht; sie werden auf
	 * den Breitensport-C-Gang abgebildet, damit der Datensatz überhaupt
	 * übertragen werden kann.
	 *
	 * @var array<string,int>
	 */
	public const AUSBILDUNGSGANG = array
	(
		'A'              => 515,   // T-A/L > Schach
		'A-B'            => 71011, // T-A/B > Schach
		'B'              => 514,   // T-B/L > Schach
		'B-B'            => 71010, // T-B/B > Schach
		'C'              => 513,   // T-C/L > Schach
		'C-B'            => 512,   // T-C/B > Schach
		'C-Sonderlizenz' => 512,   // T-C/B > Schach
		'F'              => 512,   // fiktiv, nicht angelegt beim DOSB
		'F/C'            => 512,   // fiktiv, nicht angelegt beim DOSB
		'J'              => 512,   // fiktiv, nicht angelegt beim DOSB
		'AB-Z'           => 49337, // Ausbilder-Zertifikat
	);

	/**
	 * Sekunden, die auf das Geburtsdatum aufgeschlagen werden.
	 *
	 * Contao speichert Datumsfelder als Mitternacht in der Zeitzone des
	 * Servers. Das LiMS liest den Zeitstempel als UTC; ohne diesen Aufschlag
	 * landet ein deutsches Geburtsdatum dort auf dem Vortag.
	 */
	private const ZEITKORREKTUR = 7200;

	/**
	 * Führt einen Aufruf gegen das LiMS aus.
	 *
	 * @param string             $method  Pfadbestandteil hinter der Basisadresse,
	 *                                    etwa "request" oder "download/DSchB-T-C-0002146"
	 * @param array<string,mixed>|null $data Formularfelder für den POST-Aufruf;
	 *                                    null sendet einen POST ohne Rumpf
	 * @param int                $timeout Zeitgrenze in Sekunden
	 *
	 * @return array{code:int,body:string,error:string|null} Der HTTP-Code, der
	 *         Antwortrumpf ohne Kopfzeilen und eine curl-Fehlermeldung, falls
	 *         die Verbindung gar nicht zustande kam. Ist keine Basisadresse
	 *         konfiguriert, kommt Code 0 mit einer entsprechenden Meldung
	 *         zurück.
	 */
	public function request(string $method, ?array $data = null, int $timeout = 30): array
	{
		$host = (string) Config::get('lims_host');

		if ('' === $host)
		{
			return array('code' => 0, 'body' => '', 'error' => 'Keine LiMS-Adresse in den Einstellungen hinterlegt.');
		}

		$process = curl_init($host.$method);

		if (false === $process)
		{
			return array('code' => 0, 'body' => '', 'error' => 'curl konnte nicht gestartet werden.');
		}

		curl_setopt($process, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		// Fix wegen https://github.com/icing/mod_h2/issues/167 - Mail Hetzner 25.08.2020
		curl_setopt($process, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_USERPWD, Config::get('lims_username').':'.Config::get('lims_password'));
		curl_setopt($process, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

		if (null !== $data)
		{
			curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		}

		$response = curl_exec($process);
		$error    = curl_errno($process) ? 'Curl error: '.curl_error($process) : null;
		$code     = (int) curl_getinfo($process, CURLINFO_HTTP_CODE);
		$size     = (int) curl_getinfo($process, CURLINFO_HEADER_SIZE);

		curl_close($process);

		$body = \is_string($response) ? substr($response, $size) : '';

		return array('code' => $code, 'body' => $body, 'error' => $error);
	}

	/**
	 * Überträgt eine Lizenz an das LiMS und schreibt die Antwort zurück.
	 *
	 * Bei Erfolg landen die vom DOSB vergebene Lizenznummer und die interne
	 * LiMS-Kennung im Datensatz. Unabhängig vom Ausgang werden Zeitpunkt,
	 * HTTP-Code und Antworttext des Abrufs vermerkt, damit im Backend
	 * nachvollziehbar bleibt, was zuletzt passiert ist.
	 *
	 * @param int $id Datensatz-ID in tl_lizenzverwaltung_items
	 *
	 * @return array{code:int,text:string} HTTP-Code und Klartextantwort. Gibt
	 *         es den Datensatz nicht, kommt Code 0 mit einem Hinweis zurück und
	 *         es wird nichts geschrieben.
	 */
	public function transferLicense(int $id): array
	{
		$record = $this->findRecord($id);

		if (null === $record)
		{
			return array('code' => 0, 'text' => 'Datensatz nicht gefunden');
		}

		$data   = $this->buildLicenseData($record);
		$result = $this->request('request', $data);

		Helper::log("Lizenzdaten-Transfer ID $id:\n".print_r($data, true)."\nAntwort: ".$result['body']."\nFehler: ".($result['error'] ?? '-'));

		$httpText = $this->getResponseText($result);

		if (null === $result['error'] && 200 === $result['code'])
		{
			$antwort = json_decode($result['body']);

			if (\is_object($antwort) && isset($antwort->license_number_dosb))
			{
				Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
				                       ->set(array
				                       (
				                           'license_number_dosb' => $antwort->license_number_dosb,
				                           'lid'                 => $antwort->lid ?? 0,
				                       ))
				                       ->execute($id);
			}
		}

		Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
		                       ->set(array
		                       (
		                           'dosb_tstamp'  => time(),
		                           'dosb_code'    => $result['code'],
		                           'dosb_antwort' => $httpText,
		                       ))
		                       ->execute($id);

		return array('code' => $result['code'], 'text' => $httpText);
	}

	/**
	 * Meldet eine Lizenz beim DOSB in einen untergeordneten Verband um.
	 *
	 * Der Umzug wird nur für den ersten hinterlegten Unterverband angefragt;
	 * das entspricht dem Verhalten des früheren Skripts ajaxRequestUmzug.php.
	 * Das Ergebnis wird nicht in den Datensatz geschrieben, weil der DOSB die
	 * Umbuchung erst nach Prüfung vollzieht.
	 *
	 * @param int $id Datensatz-ID in tl_lizenzverwaltung_items
	 *
	 * @return array{code:int,text:string} HTTP-Code und Klartextantwort. Ohne
	 *         DOSB-Lizenznummer oder ohne hinterlegte Untergliederung kommt
	 *         Code 0 mit einem Hinweis zurück.
	 */
	public function migrateLicense(int $id): array
	{
		$record = $this->findRecord($id);

		if (null === $record)
		{
			return array('code' => 0, 'text' => 'Datensatz nicht gefunden');
		}

		if (!$record->license_number_dosb)
		{
			return array('code' => 0, 'text' => 'Keine DOSB-Lizenznummer vorhanden');
		}

		$unterverbaende = Helper::getUntergliederung((string) $record->verband);

		if (!$unterverbaende)
		{
			return array('code' => 0, 'text' => 'Keine Untergliederung hinterlegt');
		}

		$result = $this->request('migration_request', array
		(
			'firstname'           => $record->vorname,
			'lastname'            => $record->name,
			'license_number_dosb' => $record->license_number_dosb,
			'organisation_id'     => reset($unterverbaende),
		));

		Helper::log("Umzugsanfrage ID $id: ".$result['code'].' '.$result['body']);

		return array('code' => $result['code'], 'text' => $this->getResponseText($result));
	}

	/**
	 * Stellt das Datenpaket für eine Lizenzübertragung zusammen.
	 *
	 * @param object $record Zeilenobjekt aus dem Verbund von
	 *                       tl_lizenzverwaltung_items und tl_lizenzverwaltung
	 *
	 * @return array<string,mixed> Die Formularfelder für das LiMS. Bei einer
	 *         bereits vergebenen Lizenznummer wird diese mitgeschickt, sonst
	 *         die Organisationsnummer des DSB samt Erstausstellungsdatum.
	 */
	public function buildLicenseData(object $record): array
	{
		$data = array
		(
			'firstname'          => $record->vorname,
			'lastname'           => $record->name,
			'academic_title'     => $record->titel,
			'birthdate'          => (int) $record->geburtstag + self::ZEITKORREKTUR,
			'gender'             => $record->geschlecht,
			'street'             => $record->strasse,
			'city'               => $record->ort,
			'postal'             => $record->plz,
			'mail'               => $record->email,
			'training_course_id' => self::AUSBILDUNGSGANG[$record->lizenz] ?? 0,
			'valid_until'        => $record->gueltigkeit,
			'issue_date'         => Helper::getVerlaengerung($record->erwerb, $record->verlaengerungen),
			'issue_place'        => 'Berlin',
			'honor_code'         => (int) $record->codex,
			'honor_code_date'    => $record->codex_date,
			'first_aid'          => (int) $record->help,
			'first_aid_date'     => $record->help_date,
			'custom_1'           => Helper::getVerband((string) $record->verband),
		);

		if ($record->license_number_dosb)
		{
			$data['license_number_dosb'] = $record->license_number_dosb;
		}
		else
		{
			$data['organisation_id']  = self::ORGANISATION_DSB;
			$data['first_issue_date'] = $record->erwerb;
		}

		return $data;
	}

	/**
	 * Liest einen Lizenzdatensatz samt der zugehörigen Person.
	 *
	 * @param int $id Datensatz-ID in tl_lizenzverwaltung_items
	 *
	 * @return object|null Das Zeilenobjekt, oder null wenn es den Datensatz
	 *                     nicht gibt
	 */
	public function findRecord(int $id): ?object
	{
		$result = Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                 ->limit(1)
		                                 ->execute($id);

		return $result->numRows ? $result : null;
	}

	/**
	 * Formt aus einer LiMS-Antwort einen kurzen Klartext für das Backend.
	 *
	 * Das LiMS liefert Fehlermeldungen als JSON-Zeichenkette, also in
	 * Anführungszeichen und mit umschließenden Klammern. Die werden für die
	 * Anzeige abgeschnitten; bei Erfolg steht schlicht "OK" im Datensatz.
	 *
	 * @param array{code:int,body:string,error:string|null} $result Rückgabe von request()
	 *
	 * @return string Höchstens 255 Zeichen, passend zur Spaltenbreite
	 */
	private function getResponseText(array $result): string
	{
		if (null !== $result['error'])
		{
			return substr($result['error'], 0, 255);
		}

		if (200 === $result['code'])
		{
			return 'OK';
		}

		$body = $result['body'];

		if (\strlen($body) > 4)
		{
			$body = substr($body, 2, \strlen($body) - 4);
		}

		return substr(trim($body), 0, 255);
	}
}
