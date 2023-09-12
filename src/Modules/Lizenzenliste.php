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

namespace Schachbulle\ContaoLizenzverwaltungBundle\Modules;

class Lizenzenliste extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_lizenzenliste';
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### LISTE DER LIZENZEN ###';
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
		
		$Verband = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbaende(); // Verbände holen
		$heute = time();
	
		$lizenzen = unserialize($this->lizenzverwaltung_typ); // Zu zeigende Lizenzen in SQL-String verpacken
		$jahresende = $this->lizenzverwaltung_endofyear; // Gültigkeit bis Jahresende (Boolean)

		if(is_array($lizenzen))
		{
			$sql = 'AND (tl_lizenzverwaltung_items.lizenz = \''.$lizenzen[0].'\'';
			for($x = 1; $x < count($lizenzen); $x++)
			{
				$sql .= ' OR tl_lizenzverwaltung_items.lizenz = \''.$lizenzen[$x].'\'';
			}
			$sql .= ')';
		}

		// Lizenzen einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ? AND tl_lizenzverwaltung.published = ? $sql ORDER BY tl_lizenzverwaltung.name ASC, tl_lizenzverwaltung.vorname ASC")
		                                  ->execute($heute, 1, 1);
		//$lizenzen = is_array($lizenzen) ? array_intersect($lizenzen, $result->fetchEach('id')) : $result->fetchEach('id');

		$lizenzen = array();
		if($result->numRows)
		{
			while($result->next())
			{
				$lizenzen[] = array
				(
					'nachname'    => $result->name,
					'vorname'     => $result->vorname,
					'verband'     => $Verband[$result->verband],
					'lizenz'      => $result->lizenz,
					'gueltigkeit' => ($jahresende) ? '31.12.'.date('Y', $result->gueltigkeit) : date('d.m.Y', $result->gueltigkeit),
				);
			}
		}
			
		$this->Template->lizenzview = $this->lizenzverwaltung_typview;
		$this->Template->headline = $this->headline;
		$this->Template->hl = $this->hl;
		$this->Template->trainer = $lizenzen;
		
	}

}
