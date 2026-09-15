<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Modules;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper;

/**
 * Frontend-Modul mit der Liste der gültigen Lizenzen.
 *
 * Angezeigt werden nur veröffentlichte Personen mit veröffentlichten Lizenzen,
 * deren Gültigkeit noch nicht abgelaufen ist. Welche Lizenzarten erscheinen,
 * legt die Moduleinstellung fest.
 */
class Lizenzenliste extends Module
{
	/**
	 * Name des Frontend-Templates.
	 *
	 * @var string
	 */
	protected $strTemplate = 'mod_lizenzenliste';

	/**
	 * Erzeugt die Ausgabe des Moduls.
	 *
	 * Im Backend erscheint statt der Liste ein Platzhalter, damit die
	 * Modulübersicht nicht die komplette Lizenzliste rendert. Die Konstante
	 * TL_MODE, mit der das früher geprüft wurde, gibt es unter Contao 5 nicht
	 * mehr — der Scope-Matcher leistet dasselbe in beiden Fassungen.
	 *
	 * @return string Der HTML-Code des Moduls
	 */
	public function generate()
	{
		if ($this->isBackend())
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### LISTE DER LIZENZEN ###';
			$objTemplate->title    = $this->name;
			$objTemplate->id       = $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	/**
	 * Füllt das Template mit den Lizenzdaten.
	 *
	 * @return void Setzt die Template-Variablen lizenzview und trainer. Die
	 *              Variablen headline und hl setzt bereits Module::generate(),
	 *              sie werden hier nicht noch einmal belegt.
	 */
	protected function compile()
	{
		$verbaende  = Helper::getVerbaende();
		$jahresende = (bool) $this->lizenzverwaltung_endofyear;

		$arten = StringUtil::deserialize($this->lizenzverwaltung_typ, true);

		$bedingung = '';
		$parameter = array(time(), 1, 1);

		if ($arten)
		{
			// Je Lizenzart ein Platzhalter, damit die Werte gebunden bleiben
			$bedingung = ' AND tl_lizenzverwaltung_items.lizenz IN ('.implode(', ', array_fill(0, \count($arten), '?')).')';
			$parameter = array_merge($parameter, array_values($arten));
		}

		$result = Database::getInstance()->prepare("SELECT tl_lizenzverwaltung.name, tl_lizenzverwaltung.vorname, tl_lizenzverwaltung_items.verband, tl_lizenzverwaltung_items.lizenz, tl_lizenzverwaltung_items.gueltigkeit FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ? AND tl_lizenzverwaltung.published = ?".$bedingung." ORDER BY tl_lizenzverwaltung.name ASC, tl_lizenzverwaltung.vorname ASC")
		                                 ->execute(...$parameter);

		$lizenzen = array();

		while ($result->next())
		{
			$lizenzen[] = array
			(
				'nachname'    => $result->name,
				'vorname'     => $result->vorname,
				'verband'     => $verbaende[$result->verband] ?? '',
				'lizenz'      => $result->lizenz,
				'gueltigkeit' => $jahresende ? '31.12.'.date('Y', (int) $result->gueltigkeit) : date('d.m.Y', (int) $result->gueltigkeit),
			);
		}

		$this->Template->lizenzview = $this->lizenzverwaltung_typview;
		$this->Template->trainer    = $lizenzen;
	}

	/**
	 * Stellt fest, ob die Anfrage aus dem Backend kommt.
	 *
	 * @return bool true im Backend, false im Frontend und wenn gar kein Request
	 *              vorliegt (Kommandozeile)
	 */
	private function isBackend(): bool
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		return null !== $request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request);
	}
}
