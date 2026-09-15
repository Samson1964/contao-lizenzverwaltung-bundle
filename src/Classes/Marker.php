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
use Contao\Environment;
use Contao\Input;

/**
 * Löscht die Markierungen in den Lizenzdatensätzen.
 *
 * Die Klasse hängt am Schlüssel "deleteMarker" des Backend-Moduls und wird
 * über die globale Operation gleichen Namens in tl_lizenzverwaltung erreicht.
 */
class Marker extends Backend
{
	/**
	 * Erzeugt das Objekt.
	 *
	 * Der Konstruktor muss ausdrücklich öffentlich sein: Unter Contao 4.13 ist
	 * `Backend::__construct()` nur protected, die Klasse ließe sich dort sonst
	 * von außerhalb der Contao-Klassenhierarchie nicht erzeugen.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Entfernt die Markierung aus allen markierten Lizenzen.
	 *
	 * Die Methode wird vom Backend-Modul mit dem Schlüssel "deleteMarker"
	 * aufgerufen. Sie prüft den Schlüssel noch einmal selbst, weil Contao den
	 * Rückruf auch beim Aufbau anderer Ansichten auswerten kann.
	 *
	 * @param DataContainer $dc Der Data Container der aktuellen Ansicht; wird
	 *                          nicht ausgewertet, gehört aber zur vorgegebenen
	 *                          Signatur des Modul-Rückrufs
	 *
	 * @return string Leere Zeichenkette, wenn ein anderer Schlüssel anliegt.
	 *                Im Erfolgsfall kehrt die Methode nicht zurück, sondern
	 *                leitet auf die Lizenzübersicht zurück.
	 */
	public function deleteMarker(DataContainer $dc): string
	{
		if (Input::get('key') !== 'deleteMarker')
		{
			return '';
		}

		Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items SET marker = ? WHERE marker = ?")
		                       ->execute('', '1');

		Controller::redirect(str_replace('&key=deleteMarker', '', Environment::get('request')));
	}
}
