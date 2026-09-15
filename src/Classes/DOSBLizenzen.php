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
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;

/**
 * Backend-Aktionen rund um die Lizenzen des DOSB.
 *
 * Die Klasse bedient die Schlüssel getLizenz, getLizenzPDF, getLizenzPDFCard
 * und exportDOSB des Backend-Moduls. Die eigentliche Kommunikation mit dem
 * Lizenzmanagementsystem liegt in LimsClient.
 */
class DOSBLizenzen extends Backend
{
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
	 * Erstellt oder verlängert eine einzelne Lizenz beim DOSB.
	 *
	 * Die Datensatz-ID kommt aus dem Parameter `id` der Adresse. Nach dem
	 * Abruf wird auf die Bearbeitungsmaske zurückgeleitet, damit der Anwender
	 * das Ergebnis unmittelbar im Feld "Letzter Abruf" sieht.
	 *
	 * @return void Die Methode kehrt nicht zurück, sondern leitet weiter
	 */
	public function getLizenz(): void
	{
		$id = (int) Input::get('id');

		if ($id > 0)
		{
			(new LimsClient())->transferLicense($id);
		}

		Controller::redirect(str_replace('&key=getLizenz', '&act=edit', Environment::get('request')));
	}

	/**
	 * Ruft die Lizenzurkunde im Format DIN A4 als PDF ab.
	 *
	 * @return void Die Methode kehrt nicht zurück, sondern leitet weiter
	 */
	public function getLizenzPDF(): void
	{
		$this->downloadPdf('', 'dosb_pdf');

		Controller::redirect(str_replace('&key=getLizenzPDF', '&act=edit', Environment::get('request')));
	}

	/**
	 * Ruft die Lizenzurkunde im Kartenformat als PDF ab.
	 *
	 * @return void Die Methode kehrt nicht zurück, sondern leitet weiter
	 */
	public function getLizenzPDFCard(): void
	{
		$this->downloadPdf('-card', 'dosb_pdfcard');

		Controller::redirect(str_replace('&key=getLizenzPDFCard', '&act=edit', Environment::get('request')));
	}

	/**
	 * Holt eine Lizenzurkunde vom DOSB und legt sie im Lizenzordner ab.
	 *
	 * Beide PDF-Formate unterscheiden sich nur im Dateizusatz, im
	 * mitgeschickten Formatwunsch und in den Spalten, in denen das Ergebnis
	 * vermerkt wird — deshalb eine gemeinsame Methode.
	 *
	 * @param string $suffix Dateizusatz vor der Endung; leer für DIN A4,
	 *                       "-card" für das Kartenformat
	 * @param string $prefix Spaltenpräfix für den Abrufvermerk, also
	 *                       "dosb_pdf" oder "dosb_pdfcard"
	 *
	 * @return void Schreibt die PDF-Datei in den in den Einstellungen
	 *              gewählten Lizenzordner und vermerkt Zeitpunkt, HTTP-Code
	 *              und Antworttext am Datensatz. Fehlt der Ordner oder die
	 *              Lizenznummer, passiert nichts.
	 */
	private function downloadPdf(string $suffix, string $prefix): void
	{
		$id     = (int) Input::get('id');
		$client = new LimsClient();
		$record = $id > 0 ? $client->findRecord($id) : null;

		if (null === $record || !$record->license_number_dosb)
		{
			return;
		}

		$ordner = $this->getLizenzordner();

		if (null === $ordner)
		{
			Helper::log('PDF-Abruf ID '.$id.' abgebrochen: kein Lizenzordner in den Einstellungen gewählt.');

			return;
		}

		// Das Kartenformat wird über ein Formularfeld angefordert; DIN A4 ist die Vorgabe
		$result = $client->request(
			'download/'.urlencode((string) $record->license_number_dosb),
			'' === $suffix ? null : array('format' => 'card'),
			60
		);

		if (200 === $result['code'] && null === $result['error'])
		{
			file_put_contents(Helper::getProjectDir().'/'.$ordner->path.'/'.$record->license_number_dosb.$suffix.'.pdf', $result['body']);
			$httpText = 'OK';
		}
		else
		{
			$httpText = substr($result['error'] ?? trim($result['body']), 0, 255);
		}

		Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
		                       ->set(array
		                       (
		                           $prefix.'_tstamp'  => time(),
		                           $prefix.'_code'    => $result['code'],
		                           $prefix.'_antwort' => $httpText,
		                       ))
		                       ->execute($id);

		Helper::log('PDF-Abruf ID '.$id.$suffix.': '.$result['code'].' '.$httpText);
	}

	/**
	 * Liefert den in den Einstellungen gewählten Lizenzordner.
	 *
	 * @return FilesModel|null Das Ordner-Modell, oder null wenn nichts gewählt
	 *                         ist oder der Ordner zwischenzeitlich gelöscht wurde
	 */
	private function getLizenzordner(): ?FilesModel
	{
		$uuid = $GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner'] ?? '';

		return $uuid ? FilesModel::findByUuid($uuid) : null;
	}

	/**
	 * Zeigt die Seite für den Stapelexport aller offenen Lizenzen.
	 *
	 * Ohne Parameter erscheint nur der Startknopf. Mit `start=1` werden alle
	 * zu übertragenden Datensätze aufgelistet und anschließend einzeln per
	 * Ajax an das LiMS geschickt — einzeln deshalb, weil ein Sammelaufruf über
	 * mehrere hundert Lizenzen jede Zeitgrenze reißen würde. Mit `umzug=1`
	 * läuft dasselbe für Umzugsanfragen.
	 *
	 * @return string Der HTML-Code der Backend-Seite
	 */
	public function exportToDOSB(): string
	{
		if (Input::get('start'))
		{
			return $this->renderBatch(
				"SELECT tl_lizenzverwaltung.id AS personId, tl_lizenzverwaltung_items.id AS id, tl_lizenzverwaltung.vorname, tl_lizenzverwaltung.name, tl_lizenzverwaltung_items.license_number_dosb FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ? AND (tl_lizenzverwaltung_items.letzteAenderung > tl_lizenzverwaltung_items.dosb_tstamp OR tl_lizenzverwaltung_items.dosb_code <> 200) ORDER BY tl_lizenzverwaltung.id",
				array(time(), 1),
				'contao_lizenzverwaltung_lims_export',
				'Datensätze sind zu exportieren',
				'Keine Datensätze zum Exportieren gefunden.',
				'&key=exportDOSB&start=1',
				false
			);
		}

		if (Input::get('umzug'))
		{
			return $this->renderBatch(
				"SELECT tl_lizenzverwaltung.id AS personId, tl_lizenzverwaltung_items.id AS id, tl_lizenzverwaltung.vorname, tl_lizenzverwaltung.name, tl_lizenzverwaltung_items.license_number_dosb FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ? AND tl_lizenzverwaltung_items.license_number_dosb <> ? AND (tl_lizenzverwaltung_items.letzteAenderung > tl_lizenzverwaltung_items.dosb_tstamp OR tl_lizenzverwaltung_items.dosb_code = 200) ORDER BY tl_lizenzverwaltung.id",
				array(time(), 1, ''),
				'contao_lizenzverwaltung_lims_umzug',
				'Datensätze sind umzuziehen',
				'Keine Datensätze zum Umziehen gefunden.',
				'&key=exportDOSB&umzug=1',
				true
			);
		}

		return '
<div id="dosb_export_status">
<h2 class="sub_headline">Aktive Lizenzen zum DOSB exportieren</h2>
<div class="tl_submit_container">
<a href="'.Controller::addToUrl('start=1&amp;rt='.Helper::getRequestToken()).'" class="dosb_button_mini">Export starten</a>
</div>
</div>';
	}

	/**
	 * Baut die Fortschrittsseite eines Stapellaufs.
	 *
	 * Die Seite listet alle betroffenen Datensätze auf und ruft für jeden die
	 * angegebene Route auf. Der Aufruf läuft der Reihe nach statt gleichzeitig:
	 * das LiMS quittiert parallele Anfragen mit Zeitüberschreitungen, und die
	 * Ausgabe bleibt so in der Reihenfolge der Liste lesbar.
	 *
	 * @param string             $sql          Abfrage; muss die Spalten id, vorname,
	 *                                         name und license_number_dosb liefern
	 * @param array<int,mixed>   $params       Parameter für die Abfrage
	 * @param string             $route        Name der aufzurufenden Symfony-Route
	 * @param string             $titel        Überschrift bei mindestens einem Treffer
	 * @param string             $leer         Meldung, wenn nichts gefunden wurde
	 * @param string             $backlinkPart Der aus der Adresse zu entfernende Teil
	 * @param bool               $mitNummer    Blendet die DOSB-Lizenznummer in der Liste ein
	 *
	 * @return string Der HTML-Code samt eingebettetem Skript
	 */
	private function renderBatch(string $sql, array $params, string $route, string $titel, string $leer, string $backlinkPart, bool $mitNummer): string
	{
		$result = Database::getInstance()->prepare($sql)->execute(...$params);

		$content = '<div id="dosb_export_status">[<i>'.date('d.m.Y H:i:s').'</i>] ';
		$ids     = array();

		if ($result->numRows)
		{
			$content .= '<b>'.$result->numRows.' '.$titel.' ...</b><br>';

			while ($result->next())
			{
				$ids[]    = (int) $result->id;
				$content .= '<span class="item" id="export_'.$result->id.'"> ID '.$result->id.' '
				          . ($mitNummer ? StringUtil::specialchars((string) $result->license_number_dosb).' ' : '')
				          . StringUtil::specialchars($result->vorname.' '.$result->name).' ...</span><br>';
			}
		}
		else
		{
			$content .= '<b>'.$leer.'</b><br>';
		}

		$backlink = str_replace($backlinkPart, '', Environment::get('request'));

		$content .= '<div style="margin-top:30px;"><a href="'.StringUtil::specialchars($backlink).'" class="dosb_button_mini">Zurück zur Lizenzverwaltung</a></div>';
		$content .= '</div>';

		// Basisadresse ohne die ID; die hängt das Skript je Datensatz an
		$url = System::getContainer()->get('router')->generate($route, array('id' => 0));
		$url = substr($url, 0, -1);

		$content .= '<script>'."\n";
		$content .= '(function () {'."\n";
		$content .= '  var records = '.json_encode($ids).';'."\n";
		$content .= '  var base = '.json_encode($url, JSON_UNESCAPED_SLASHES).';'."\n";
		$content .= '  function step(i) {'."\n";
		$content .= '    if (i >= records.length) { return; }'."\n";
		$content .= '    fetch(base + records[i], {credentials: "same-origin"})'."\n";
		$content .= '      .then(function (r) { return r.json(); })'."\n";
		$content .= '      .then(function (item) {'."\n";
		$content .= '        var el = document.getElementById("export_" + records[i]);'."\n";
		$content .= '        if (el) { el.insertAdjacentHTML("afterbegin", item.datum); el.insertAdjacentHTML("beforeend", item.text); el.style.color = item.color; }'."\n";
		$content .= '      })'."\n";
		$content .= '      .catch(function () {})'."\n";
		$content .= '      .then(function () { step(i + 1); });'."\n";
		$content .= '  }'."\n";
		$content .= '  step(0);'."\n";
		$content .= '})();'."\n";
		$content .= '</script>'."\n";

		return $content;
	}
}
