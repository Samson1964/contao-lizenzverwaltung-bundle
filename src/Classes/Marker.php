<?php

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Class dsb_trainerlizenzExport
  */
class Marker extends \Backend
{

	/**
	 * Funktion deleteMarker
	 * =========================
	 * Löscht die Markierungen in den Lizenzdatensätzen
	 */
	public function deleteMarker(\DataContainer $dc)
	{
		if ($this->Input->get('key') != 'deleteMarker')
		{
			return '';
		}

		// Markierungen löschen
		$result = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items SET marker = ? WHERE marker = ?")
		                                  ->execute('', '1');
		// Zurück zur Seite
		\Controller::redirect(str_replace('&key=deleteMarker', '', \Environment::get('request')));
	}

}
