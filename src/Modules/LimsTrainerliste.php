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
use Contao\Module;
use Contao\System;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsTrainerliste as TrainerlisteDienst;

/**
 * Frontend-Modul „Trainerliste aus dem LiMS“.
 *
 * Gibt die Trainer mit gültiger A-, B- oder C-Lizenz beziehungsweise die
 * DOSB-Ausbilder aus, unmittelbar aus dem Lizenzmanagementsystem des DOSB.
 * Die Tabellen der Lizenzverwaltung werden dafür weder gelesen noch
 * beschrieben.
 */
class LimsTrainerliste extends Module
{
	/**
	 * Name des Frontend-Templates.
	 *
	 * @var string
	 */
	protected $strTemplate = 'mod_lims_trainerliste';

	/**
	 * Erzeugt die Ausgabe des Moduls.
	 *
	 * Im Backend erscheint nur ein Platzhalter. Das ist hier mehr als Kosmetik:
	 * Die Modulübersicht würde sonst bei leerem Cache einen Vollabruf beim
	 * LiMS auslösen.
	 *
	 * @return string Der HTML-Code des Moduls
	 */
	public function generate()
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if (null !== $request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### TRAINERLISTE AUS DEM LIMS: '.(TrainerlisteDienst::ARTEN[$this->lizenzverwaltung_lims_art]['titel'] ?? '?').' ###';
			$objTemplate->title    = $this->name;
			$objTemplate->id       = $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	/**
	 * Füllt das Template.
	 *
	 * @return void Setzt die Template-Variablen art, titel, eintraege, stand,
	 *              veraltet und endofyear. headline und hl setzt bereits
	 *              Module::generate().
	 */
	protected function compile()
	{
		$art   = (string) $this->lizenzverwaltung_lims_art;
		$liste = System::getContainer()->get(TrainerlisteDienst::class)->getListe($art);

		$this->Template->art       = $art;
		$this->Template->titel     = TrainerlisteDienst::ARTEN[$art]['titel'] ?? '';
		$this->Template->eintraege = $liste['eintraege'];
		$this->Template->stand     = $liste['stand'];
		$this->Template->veraltet  = $liste['veraltet'];
		$this->Template->endofyear = (bool) $this->lizenzverwaltung_endofyear;
	}
}
