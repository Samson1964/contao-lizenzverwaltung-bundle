<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   DeWIS
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

class Trainerliste extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_trainerliste';
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_dewis');

			$objTemplate->wildcard = '### LISTE DER TRAINERLIZENZEN ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		else
		{
		}

		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		
		$Verband = \Schachbulle\ContaoLizenzverwaltungBundle\Helper::getVerbaende(); // Verbände holen
		$heute = time();
	
		// Zu zeigende Lizenzen in SQL-String verpacken
		$lizenzen = unserialize($this->trainerlizenzen_typ);
		$sql = 'lizenz = \''.$lizenzen[0].'\'';
		for($x = 1; $x < count($lizenzen); $x++)
		{
			$sql .= ' OR lizenz = \''.$lizenzen[$x].'\'';
		}

		// Trainer-Datensatz einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_trainerlizenzen WHERE ($sql) AND gueltigkeit >= ? AND published = ? ORDER BY name ASC, vorname ASC")
										   ->execute($heute, 1);

		$trainerArr = array();
		if($result->numRows)
		{
			while($result->next())
			{
				$trainerArr[] = array
				(
					'nachname'    => $result->name,
					'vorname'     => $result->vorname,
					'verband'     => $Verband[$result->verband],
					'lizenz'      => $result->lizenz,
					'gueltigkeit' => date('d.m.Y', $result->gueltigkeit),
				);
			}
		}
			
		$this->Template->lizenzview = $this->trainerlizenzen_typview;
		$this->Template->headline = $this->headline;
		$this->Template->hl = $this->hl;
		$this->Template->trainer = $trainerArr;
		
	}

}
