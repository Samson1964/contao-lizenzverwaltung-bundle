<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2017 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * Modul Trainerlizenzen - Helper
 *
 * PHP version 5
 * @copyright  Frank Hoppe 2014 - 2017
 * @author     Frank Hoppe
 * @package    Trainerlizenzen
 * @license    LGPL
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

class Helper extends \Frontend
{
	/**
	 * Current object instance
	 * @var object
	 */
	protected static $instance = null;

	var $user;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Benutzerdaten laden
		if(FE_USER_LOGGED_IN)
		{
			// Frontenduser eingeloggt
			$this->user = \FrontendUser::getInstance();
		}
		parent::__construct();
	}


	/**
	 * Return the current object instance (Singleton)
	 * @return BannerCheckHelper
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper();
		}

		return self::$instance;
	}

	/**
	 * Liefert das Datum der letzten Verlängerung
	 * @param erwerb           Timestamp des Lizenzerwerbs
	 * @param verlaengerungen  Serialisiertes Array mit den Timestamps in aufsteigender Reihenfolge
	 * @return int             Timestamp der letzten Verlängerung oder FALSE
	 */
	public function getVerlaengerung($erwerb, $verlaengerungen)
	{
		$return = false;
		if($verlaengerungen)
		{
			$temp = unserialize($verlaengerungen);
			if($temp)
			{
				foreach($temp as $item)
				{
					$return = $item['datum'];
				}
			}
		}
		return $return ? $return : $erwerb;

	}

	public function getVerbaende()
	{

		$return = array();

		// Fix in 4.1.3: composer:install wirft Fehler aus, wenn nicht vorher geprüft wird, ob die Tabelle existiert
		$result = \Database::getInstance()->prepare("SHOW TABLES LIKE ?")
		                                  ->execute('%tl_lizenzverwaltung_verbaende%');
		if($result->numRows)
		{
			$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_verbaende WHERE published = ?")
			                                  ->execute(1);
			// Auswerten
			if($result->numRows)
			{
				while($result->next())
				{
					$return[$result->kennzeichen] = $result->name;
				}
			}
		}

		return $return;

		// Altes Format
		return array
		(
			'S'                   => 'Deutscher Schachbund',
			'1'                   => 'Baden',
			'2'                   => 'Bayern',
			'3'                   => 'Berlin',
			'D'                   => 'Brandenburg',
			'B'                   => 'Bremen',
			'4'                   => 'Hamburg',
			'5'                   => 'Hessen',
			'E'                   => 'Mecklenburg-Vorpommern',
			'7'                   => 'Niedersachsen',
			'6'                   => 'Nordrhein-Westfalen',
			'8'                   => 'Rheinland-Pfalz',
			'9'                   => 'Saarland',
			'F'                   => 'Sachsen',
			'G'                   => 'Sachsen-Anhalt',
			'A'                   => 'Schleswig-Holstein',
			'H'                   => 'Thüringen',
			'C'                   => 'Württemberg'
		);
	}

	public function getUntergliederung($kennzeichen)
	{

		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen=? AND published=?")
		                                  ->limit(1)
		                                  ->execute($kennzeichen, 1);
		$return = array();
		// Treffer?
		if($result->numRows && $result->organisation)
		{
			// Untergeordnete Verbandsnummern zurückgeben
			$daten = unserialize($result->organisation);
			foreach($daten as $item)
			{
				$return[] = $item['organisation_id'];
			}
		}
		return $return;
	}

	public function getVerband($verband)
	{
		$verbaende = self::getVerbaende();
		return $verbaende[$verband];
	}

	public function getLizenzen()
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
	 * Sortiert ein multidimensionales Array der Form
	 * $data = array(
	 *   array("jahrgang" => 1979, "nachname" => "Müller",   "vorname" => "Annegret"),
	 *   array("jahrgang" => 1983, "nachname" => "Schmidt",  "vorname" => "Lisbeth"),
	 *   array("jahrgang" => 1980, "nachname" => "Meier",    "vorname" => "Günther"),
	 *   array("jahrgang" => 1981, "nachname" => "Werner",   "vorname" => "Gabi"),
	 *   array("jahrgang" => 1980, "nachname" => "Hofmann",  "vorname" => "Ramona"),
	 *   array("jahrgang" => 1981, "nachname" => "Kaufmann", "vorname" => "Maria"),
	 * );
	 * indem das Array vorher umgebaut wird:
	 * foreach ($data as $key => $row) {
	 *   $jahrgang[$key] = $row['jahrgang'];
	 *   $nachname[$key] = $row['nachname'];
	 * }
	 *
	 * Aufruf mit z.B.:
	 * $sorted = sortArrayByFields(
	 *     $data,
	 *     array(
	 *         'jahrgang' => SORT_DESC,
	 *         'nachname' => array(SORT_ASC, SORT_STRING)
	 *     )
	 * );
	 *
	 * http://www.karlvalentin.de/660/mehrdimensionale-arrays-in-php-bequem-sortieren.html
	 */
	function sortArrayByFields($arr, $fields)
	{
		$sortFields = array();
		$args       = array();

		foreach ($arr as $key => $row)
		{
			foreach ($fields as $field => $order)
			{
				$sortFields[$field][$key] = $row[$field];
			}
		}

		foreach ($fields as $field => $order)
		{
			$args[] = $sortFields[$field];

			if (is_array($order))
			{
				foreach ($order as $pt)
				{
					$args[$pt];
				}
			}
			else
			{
				$args[] = $order;
			}
		}

		$args[] = &$arr;

		call_user_func_array('array_multisort', $args);

		return $arr;
	}

	/**
	 * Funktion getVerbandsmails
	 * =========================
	 * Liest die Mailadressen der Verbände ein oder gibt die Mailadresse eines Verbands zurück
	 */
	public function getVerbandsmails()
	{
		static $verbandsmails;

		if(!$verbandsmails)
		{
			// Referenten einlesen
			$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE published = ?")
			                                  ->execute(1);
			// Auswerten
			if($result->numRows)
			{
				while($result->next())
				{
					$verbandsmails[$result->verband] = $result->email;
				}
			}
		}

		return $verbandsmails;
	}

	/**
	 * Funktion getVerbandsmail
	 * =========================
	 */
	public function getVerbandMail($verband)
	{
		$verbandsliste = self::getVerbandsmails();

		return $verbandsliste[$verband];
	}

	/**
	 * Funktion getPersonMail
	 * =========================
	 * Liest die Mailadressen der Verbände ein oder gibt die Mailadresse eines Verbands zurück
	 */
	public function getPersonMail($id)
	{
		// Person einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung WHERE id = ?")
		                                  ->execute($id);
		// Auswerten
		if($result->numRows)
		{
			return $result->email;
		}

		return false;
	}

}