<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @package   Trainerlizenzen
 * @author    Frank Hoppe <webmaster@schachbund.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Frank Hoppe 2014 - 2017
 */

$GLOBALS['TL_CSS'][] = 'system/modules/trainerlizenzen/assets/css/default.css';

/**
 * Table tl_lizenzverwaltung
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_lizenzverwaltung_items', 'tl_lizenzverwaltung_mails'),
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_lizenzverwaltung', 'applyAdvancedFilter'),
			array('tl_lizenzverwaltung', 'getVerbandsmails'),
		), 
		'sql' => array
		(
			'keys' => array
			(
				'id'             => 'primary',
				'vorname'        => 'index',
				'name'           => 'index'
			)
		)
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('name ASC', 'vorname ASC'),
			'flag'                    => 11,
			'panelLayout'             => 'myfilter;filter;search,sort,limit',
			'panel_callback'          => array('myfilter' => array('tl_lizenzverwaltung', 'generateAdvancedFilter')),  
		),
		'label' => array
		(
			'fields'                  => array('name', 'vorname', 'email', 'plz', 'ort', 'strasse'),
			'showColumns'             => true,
		),
		'global_operations' => array
		(
			'referenten' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['referenten'],
				'href'                => 'table=tl_lizenzverwaltung_referenten',
				'icon'                => 'bundles/contaotrainerlizenzen/images/referenten.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'exportDOSB' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportDOSB'],
				'href'                => 'key=exportDOSB',
				'icon'                => 'bundles/contaotrainerlizenzen/images/export.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['export'],
				'href'                => 'key=export',
				'icon'                => 'bundles/contaotrainerlizenzen/images/exportCSV.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'exportXLS' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportXLS'],
				'href'                => 'key=exportXLS',
				'icon'                => 'bundles/contaotrainerlizenzen/images/exportEXCEL.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['edit'],
				'href'                => 'table=tl_lizenzverwaltung_items',
				'icon'                => 'edit.gif'
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
			), 
			'email' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['emailbox'],
				'href'                => 'table=tl_lizenzverwaltung_mails',
				'icon'                => 'bundles/contaotrainerlizenzen/images/email.png',
				'button_callback'     => array('tl_lizenzverwaltung', 'toggleEmail')
			), 
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_lizenzverwaltung', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('addEnclosure'),
		'default'                     => '{name_legend},vorname,name,titel,geburtstag,geschlecht;{adresse_legend},strasse,plz,ort,email,telefon;{hinweise_legend:hide},addEnclosure,bemerkung,migrationInfo;{published_legend},published'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'addEnclosure'                => 'enclosure,enclosureInfo'
	), 

	// Base fields in table tl_lizenzverwaltung
	'fields' => array
	(
		'id' => array
		(
			'search'                  => true,
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['tstamp'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// Vorname
		'vorname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['vorname'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'w50'
			)
		),
		// Nachname
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['name'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'w50'
			)
		),
		// Titel
		'titel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['titel'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(10) NOT NULL default ''",
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		),
		// Geburtstag
		'geburtstag' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geburtstag'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 8,
			'filter'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true,
				'rgxp'                => 'date', 
				'datepicker'          => true,
				'tl_class'            => 'w50 wizard clr'
			),
			'sql'                     => "varchar(11) NOT NULL default ''",
		),
		// Geschlecht
		'geschlecht' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geschlecht'],
			'inputType'               => 'select',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 12,
			'filter'                  => true,
			'sql'                     => "varchar(1) NOT NULL default ''",
			'options'                 => array
			(
				'-'                   => '-',
				'm'                   => 'männlich',
				'w'                   => 'weiblich',
			),
			'eval'                    => array
			(
				'mandatory'           => true,
				'chosen'              => true,
				'tl_class'            => 'w50'
			)
		),
		// Straße
		'strasse' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['strasse'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'explanation'             => 'trainerlizenzen_strasse', 
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => true,
				'helpwizard'          => true,
				'tl_class'            => 'w50'
			)
		),
		// PLZ
		'plz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['plz'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'explanation'             => 'trainerlizenzen_plz', 
			'filter'                  => false,
			'sql'                     => "varchar(32) NOT NULL default ''",
			'eval'                    => array
			(
				'minlength'           => 5,
				'maxlength'           => 8,
				'mandatory'           => true,
				'helpwizard'          => true,
				'tl_class'            => 'w50 clr'
			)
		),
		// Ort
		'ort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['ort'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'explanation'             => 'trainerlizenzen_strasse', 
			'eval'                    => array
			(
				'mandatory'           => true,
				'helpwizard'          => true,
				'tl_class'            => 'w50'
			)
		),
		// Email
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['email'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'rgxp'                => 'email',
				'tl_class'            => 'w50 clr'
			)
		),
		// Telefon
		'telefon' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['telefon'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => false,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'w50'
			)
		),
		'addEnclosure' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['addEnclosure'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'submitOnChange'      => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'enclosure' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosure'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			//'load_callback'           => array(array('tl_lizenzverwaltung', 'viewFiles')),
			'eval'                    => array
			(
				'multiple'            => true,
				'fieldType'           => 'checkbox',
				'filesOnly'           => true,
				'isDownloads'         => true,
				'extensions'          => Config::get('allowedDownload'),
				'mandatory'           => true
			),
			'sql'                     => "blob NULL"
		),
		// Gibt Informationen zu den Dateien im Feld enclosure aus
		'enclosureInfo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'],
			'input_field_callback'    => array('tl_lizenzverwaltung', 'viewEnclosureInfo'),
		),
		'bemerkung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['bemerkung'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array('rte' => 'tinyMCE', 'cols' => 80,'rows' => 5, 'style' => 'height: 80px'),
			'sql'                     => "text NULL"
		),
		'migrationInfo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['migrationInfo'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array('rte' => 'tinyMCE', 'cols' => 80,'rows' => 5, 'style' => 'height: 80px'),
			'sql'                     => "text NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['published'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'filter'                  => true,
			'exclude'                 => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
	),
);

class tl_lizenzverwaltung extends \Backend
{
	
	var $verbandsmail = array();

	public function toggleEmail($row, $href, $label, $title, $icon, $attributes)
	{
		$this->import('BackendUser', 'User');

		$href .= '&amp;table=tl_lizenzverwaltung_mails&amp;id='.$row['id'];

		if($row['email'] && $this->verbandsmail[$row['verband']])
		{
			$icon = 'bundles/contaotrainerlizenzen/images/email.png';
		}
		elseif($row['email'] || $this->verbandsmail[$row['verband']])
		{
			$icon = 'bundles/contaotrainerlizenzen/images/email_gelb.png';
		}
		else
		{
			$icon = 'bundles/contaotrainerlizenzen/images/email_grau.png';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		$this->import('BackendUser', 'User');

		if (strlen($this->Input->get('tid')))
		{
			$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_lizenzverwaltung::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	public function toggleVisibility($intId, $blnPublished)
	{
		// Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_lizenzverwaltung::published', 'alexf'))
		{
			$this->log('Kein Zugriffsrecht für Aktivierung Datensatz ID "'.$intId.'"', 'tl_lizenzverwaltung toggleVisibility', TL_ERROR);
			// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
			if (version_compare(VERSION, '4.0', '>='))
			{
				$backlink = \System::getContainer()->get('router')->generate('contao_backend');
			}
			else
			{
				$backlink = 'contao/main.php';
			}
			$this->redirect($backlink.'?act=error');
		}
		
		$this->createInitialVersion('tl_lizenzverwaltung', $intId);
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_lizenzverwaltung']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_lizenzverwaltung']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}
		
		// Update the database
		$this->Database->prepare("UPDATE tl_lizenzverwaltung SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
					   ->execute($intId);
		$this->createNewVersion('tl_lizenzverwaltung', $intId);
	}

	/**
	 * Add an image to each record
	 * @param array         $row (Assoziatives Array mit allen Werten des aktuellen Datensatzes)
	 * @param string        $label (Wert des erstes sichtbaren Wertes des aktuellen Datensatzes)
	 * @param DataContainer $dc
	 * @param array         $args (Numerisches Array mit den sichtbaren Werten des aktuellen Datensatzes)
	 *
	 * @return array
	 */
	public function convertDate($row, $label, DataContainer $dc, $args)
	{

		for($x=0;$x<count($args);$x++)
		{
			$args[$x] = \Samson\Helper::getDate($args[$x]);
		}
		return $args; 
	} 

	public function getLizenznummer(DataContainer $dc)
	{

		// Lizenzstatus
		if($dc->activeRecord->license_number_dosb)
		{
			$status = '<b>'.$dc->activeRecord->license_number_dosb.'&nbsp;&nbsp;</b><a href="'.LIMS_LINK.'dosb_license/'.$dc->activeRecord->lid.'" target="_blank" class="dosb_button_mini">Ansehen</a>';
		}
		else
		{
			$status = 'Keine DOSB-Lizenz vorhanden';
		}
		
		$string = '
<div class="w50 widget" style="height:45px;">
	'.$status.'
</div>'; 
		
		return $string;
	}

	public function getLizenzbutton(DataContainer $dc)
	{
		// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
		if (version_compare(VERSION, '4.0', '>='))
		{
			$link = \System::getContainer()->get('router')->generate('contao_backend');
		}
		else
		{
			$link = 'contao/main.php';
		}
		$link .= '?do=trainerlizenzen&amp;key=getLizenz&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_tstamp).' ('.$dc->activeRecord->dosb_code.' '.$dc->activeRecord->dosb_antwort.')';
		}
		else $antwort = '';
		
		$string = '
<div class="w50 widget" style="height:45px;">
	<a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['button_license'][0].'</a>
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$antwort.'</p>
</div>'; 
		
		return $string;
	}
	
	/**
	 * Link zum PDF im DIN-A4-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function getLizenzPDFView(DataContainer $dc)
	{
		// Links zum PDF generieren
		$pdf_server = TL_ROOT.'/files/trainerlizenzen/'.$dc->activeRecord->license_number_dosb.'.pdf';
		$pdf_download = 'files/trainerlizenzen/'.$dc->activeRecord->license_number_dosb.'.pdf';
		
		// Lizenzstatus
		if($dc->activeRecord->license_number_dosb && file_exists($pdf_server))
		{
			$pdf_datum = date('d.m.Y H:i:s', filemtime($pdf_server));
			$status = '<a href="'.$pdf_download.'" target="_blank" title="Zeigt die auf dem DSB-Server gespeicherte Lizenzurkunde an." class="dosb_button_mini">PDF DIN A4 anzeigen</a> PDF-Datum: '.$pdf_datum;
			//$email = '&nbsp;<a href="" title="Verschickt die Lizenzurkunde mit der Standard-Mailvorlage an den Trainer, den Landesverband und den DSB" class="dosb_button_mini">PDF verschicken</a>';
			$email = '';
		}
		else
		{
			$status = 'Kein PDF DIN A4 vorhanden';
			$email = '';
		}
		
		if($dc->activeRecord->license_number_dosb)
		{
		$string = '
<div class="w50 widget" style="height:45px;">
	'.$status.$email.'
</div> '; 
			return $string;
		}
		else return '';
	}

	/**
	 * Link zum PDF im Karten-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function getLizenzPDFCardView(DataContainer $dc)
	{
		// Links zum PDF generieren
		$pdf_server = TL_ROOT.'/files/trainerlizenzen/'.$dc->activeRecord->license_number_dosb.'-card.pdf';
		$pdf_download = 'files/trainerlizenzen/'.$dc->activeRecord->license_number_dosb.'-card.pdf';

		// Lizenzstatus
		if($dc->activeRecord->license_number_dosb && file_exists($pdf_server))
		{
			$pdf_datum = date('d.m.Y H:i:s', filemtime($pdf_server));
			$status = '<a href="'.$pdf_download.'" target="_blank" title="Zeigt die auf dem DSB-Server gespeicherte Lizenzurkunde im Format Card an." class="dosb_button_mini">PDF Karte anzeigen</a> PDF-Datum: '.$pdf_datum;
			//$email = '&nbsp;<a href="" title="Verschickt die Lizenzurkunde mit der Standard-Mailvorlage an den Trainer, den Landesverband und den DSB" class="dosb_button_mini">PDF verschicken</a>';
			$email = '';
		}
		else
		{
			$status = 'Kein PDF Card vorhanden';
			$email = '';
		}
		
		if($dc->activeRecord->license_number_dosb)
		{
		$string = '
<div class="w50 widget" style="height:45px;">
	'.$status.$email.'
</div> '; 
			return $string;
		}
		else return '';
	}

	/**
	 * Button zum PDF-Abruf im DIN-A4-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function getLizenzPDF(DataContainer $dc)
	{

		// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
		if (version_compare(VERSION, '4.0', '>='))
		{
			$link = \System::getContainer()->get('router')->generate('contao_backend');
		}
		else
		{
			$link = 'contao/main.php';
		}
		$link .= '?do=trainerlizenzen&amp;key=getLizenzPDF&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;
		
		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_pdf_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_pdf_tstamp).' ('.$dc->activeRecord->dosb_pdf_code.' '.$dc->activeRecord->dosb_pdf_antwort.')';
		}
		else $antwort = '';

		if($dc->activeRecord->license_number_dosb)
		{
		$string = '
<div class="w50 widget" style="height:45px;">
	<a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['button_pdf'][0].'</a>
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$antwort.'</p>
</div>'; 
			return $string;
		}
		else return '';
			
	}

	/**
	 * Button zum PDF-Abruf im Karten-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function getLizenzPDFCard(DataContainer $dc)
	{

		// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
		if (version_compare(VERSION, '4.0', '>='))
		{
			$link = \System::getContainer()->get('router')->generate('contao_backend');
		}
		else
		{
			$link = 'contao/main.php';
		}
		$link .= '?do=trainerlizenzen&amp;key=getLizenzPDFCard&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_pdfcard_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_pdfcard_tstamp).' ('.$dc->activeRecord->dosb_pdfcard_code.' '.$dc->activeRecord->dosb_pdfcard_antwort.')';
		}
		else $antwort = '';

		if($dc->activeRecord->license_number_dosb)
		{
		$string = '
<div class="w50 widget" style="height:45px;">
	<a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['button_pdfcard'][0].'</a>
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$antwort.'</p>
</div>'; 
			return $string;
		}
		else return '';
			
	}

	/**
	 * Setzt das aktuelle Datum beim Änderungsdatum
	 * (Noch nicht weitergebaut, deshalb display:none; Unklar wwas aufgerufen werden soll und wie man das Feld per Button ändert.)
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function setHeute(DataContainer $dc)
	{
		// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
		if (version_compare(VERSION, '4.0', '>='))
		{
			$link = \System::getContainer()->get('router')->generate('contao_backend');
		}
		else
		{
			$link = 'contao/main.php';
		}
		$link .= '?do=trainerlizenzen&amp;key=getLizenz&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_tstamp).' ('.$dc->activeRecord->dosb_code.' '.$dc->activeRecord->dosb_antwort.')';
		}
		else $antwort = '';
		
		$string = '
<div class="w50 widget" style="display:none">
	<a href="#" onclick="AjaxRequest.toggleSubpalette(this, \'sub_login\', \'login\')" onfocus="Backend.getScrollOffset()" class="dosb_button_mini">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['setHeute'][0].'</a>
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['setHeute'][1].'</p>
</div>'; 
		
		return $string;
	}
	
	public function getVerification(DataContainer $dc)
	{

		// ----------------------------------------------------------------
		// GÜLTIGKEIT DER LIZENZ
		// ----------------------------------------------------------------
		// Letztes Verlängerungsdatum ermitteln
		$verlaengerung = \Samson\Trainerlizenzen\Helper::getVerlaengerung($dc->activeRecord->erwerb, $dc->activeRecord->verlaengerungen);

		// Zulässiges Gültigkeitsdatum feststellen
		switch(substr($dc->activeRecord->lizenz,0,1))
		{
			case 'A': // 2 Jahre ab Ausstellungsdatum - 1 Tag
				//echo "|$verlaengerung|";
				$gueltigkeit = strtotime('+2 years', $verlaengerung) - 86400;
				$gueltigkeit = $this->getQuartalsende($gueltigkeit);
				break;
			case 'B': // 4 Jahre ab Ausstellungsdatum - 1 Tag
			case 'C':
				$gueltigkeit = strtotime('+4 years', $verlaengerung) - 86400;
				$gueltigkeit = $this->getQuartalsende($gueltigkeit);
				break;
			default:
				$gueltigkeit = 0;
		}

		// Gültigkeitsdatum überprüfen
		if($dc->activeRecord->license_number_dosb)
		{
			// Gültigkeitsregeln des DOSB gelten
			if($dc->activeRecord->gueltigkeit > $gueltigkeit)
			{
				Message::addError('Gültig bis ('.date('d.m.Y', $dc->activeRecord->gueltigkeit).') ist größer als erlaubt. Der DOSB erlaubt nur den '.date('d.m.Y', $gueltigkeit).'!'); 
			}
		}
		else
		{
			// Kulanzregelung DOSB für Bestandsdaten
			if($dc->activeRecord->gueltigkeit > $gueltigkeit)
			{
				Message::addInfo('Gültig bis ('.date('d.m.Y', $dc->activeRecord->gueltigkeit).') ist größer als erlaubt. Der DOSB erlaubt nur den '.date('d.m.Y', $gueltigkeit).'! Es wird Probleme bei Updates geben.'); 
			}
		}
		
		// ----------------------------------------------------------------
		// E-MAIL
		// ----------------------------------------------------------------
		if(!$dc->activeRecord->email && $dc->activeRecord->tstamp)
		{
			// Fehlende E-Mail-Adresse bei nicht neuem Datensatz
			Message::addError('E-Mail-Adresse des Trainers fehlt! Ein automatischer Lizenzversand an ihn ist nicht möglich.'); 
		}

		return '';

	}


	/**
	 * Ermittelt das Quartalsende als Timestamp für einen beliebigen Zeitstempel
	 * @param timestamp     $value (beliebiger Zeitstempel)
	 *
	 * @return timestamp
	 */
	public function getQuartalsende($value)
	{
		$quartals = array
		(
			 1 => 1,
			 2 => 1,
			 3 => 1,
			 4 => 2,
			 5 => 2,
			 6 => 2,
			 7 => 3,
			 8 => 3,
			 9 => 3,
			10 => 4,
			11 => 4,
			12 => 4
		); 

		$year = date('Y', $value);
		$quartal = $quartals[date("n", $value)]; // n = Monat 1-12

		//log_message(date('d.m.Y', $value), 'trainerlizenzen_quartal.log');
		//log_message($quartal, 'trainerlizenzen_quartal.log');
		
		switch($quartal)
		{
			case 1:
				return mktime(0, 0, 0, 3, 31, $year);
			case 2:
				return mktime(0, 0, 0, 6, 30, $year);
			case 3:
				return mktime(0, 0, 0, 9, 30, $year);
			case 4:
				return mktime(0, 0, 0, 12, 31, $year);
			default:
				return 0;
		}
	}


	public function getDate($value)
	{
		return mktime(0, 0, 0, substr($value, 4, 2), substr($value, 6, 2), substr($value, 0, 4));
	}

    public function generateAdvancedFilter(DataContainer $dc)
    {
    
        if (\Input::get('id') > 0) {
            return '';
        }

        $session = \Session::getInstance()->getData();

        // Filters
        $arrFilters = array
        (
            'tli_filter'   => array
            (
                'name'    => 'tli_filter',
                'label'   => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_extended'],
                'options' => array
				(
					'1' => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_activetrainers'], 
					'2' => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_unsentmails'], 
				)
            ),
        );

        $strBuffer = '
<div class="tl_filter tli_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter'] . ':</strong> ' . "\n";

        // Generate filters
        foreach ($arrFilters as $arrFilter) 
        {
            $strOptions = '
  <option value="' . $arrFilter['name'] . '">' . $arrFilter['label'] . '</option>
  <option value="' . $arrFilter['name'] . '">---</option>' . "\n";

            // Generate options
            foreach ($arrFilter['options'] as $k => $v) 
            {
                $strOptions .= '  <option value="' . $k . '"' . (($session['filter']['tl_registerFilter'][$arrFilter['name']] === (string) $k) ? ' selected' : '') . '>' . $v . '</option>' . "\n";
            }

            $strBuffer .= '<select name="' . $arrFilter['name'] . '" id="' . $arrFilter['name'] . '" class="tl_select' . (isset($session['filter']['tl_registerFilter'][$arrFilter['name']]) ? ' active' : '') . '">
' . $strOptions . '
</select>' . "\n";
        }

        return $strBuffer . '</div>'; 

    }  

	public function viewEnclosureInfo(DataContainer $dc)
	{
		// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
		if (version_compare(VERSION, '4.0', '>='))
		{
			$link = \System::getContainer()->get('router')->generate('contao_backend');
		}
		else
		{
			$link = 'contao/main.php';
		}
		$link .= '?do=trainerlizenzen&amp;key=getLizenz&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->enclosure)
		{
			$info = unserialize($dc->activeRecord->enclosure);
			if(is_array($info))
			{
				$content = '<ul>';
				foreach($info as $item)
				{
					$content .= '<li style="clear:both;">';
					// Suche nach UUID
					$objFile = \FilesModel::findByUuid($item); // FilesModel Objekt
					$arrMeta = $objFile ? deserialize($objFile->meta) : array(); // Metadaten extrahieren
					// Dateityp feststellen und Vorschauausgabe vorbereiten
					switch($objFile->extension)
					{
						case 'jpg':
						case 'png':
						case 'gif':
							$lightbox = "onclick=\"Backend.openModalIframe({'width':735,'height':405,'title':'Großansicht','url':".$objFile->path."})\"";
							$content .= '<a href="'.$objFile->path.'" '.$lightbox.'><img src="'.\Image::get($objFile->path, 80, 80, 'crop').'" style="float:left; margin-right:5px; margin-bottom:5px;"></a> ';
							break;
						default:
							$content .= '';
					}
					$content .= $objFile->path.'<br>';
					$content .= '<i>'.$arrMeta['de']['title'].'</i>';
					$content .= '</li>';
					//$antwort .= print_r($objFile, true);
				}
				$content .= '<li style="clear:both;"></li>';
				$content .= '</ul>';
				$antwort .= $content;
			}
		}
		else $antwort = '';
		
		$string = '
<div class="clr widget">
	<h3><label for="ctrl_enclosureInfo">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'][0].'</label></h3>
	'.$antwort.'
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'][1].'</p>
</div>'; 
		
		return $string;
	}

	public function applyAdvancedFilter()
	{

		$session = \Session::getInstance()->getData();

		// Store filter values in the session
		foreach ($_POST as $k => $v) 
		{
			if (substr($k, 0, 4) != 'tli_') 
			{
				continue;
			}

			// Reset the filter
			if ($k == \Input::post($k)) 
			{
				unset($session['filter']['tl_registerFilter'][$k]);
			} // Apply the filter
			else {
				$session['filter']['tl_registerFilter'][$k] = \Input::post($k);
			}
		}

		$this->Session->setData($session);

		if (\Input::get('id') > 0 || !isset($session['filter']['tl_registerFilter'])) 
		{
			return;
		}

		$arrPlayers = null;


		switch ($session['filter']['tl_registerFilter']['tli_filter']) 
		{
			case '1': // Alle Trainer mit noch gültigen Lizenzen
				$objPlayers = \Database::getInstance()->prepare("SELECT id FROM tl_lizenzverwaltung WHERE gueltigkeit >= ? AND published = ?")
													  ->execute(time(), 1);
				$arrPlayers = is_array($arrPlayers) ? array_intersect($arrPlayers, $objPlayers->fetchEach('id')) : $objPlayers->fetchEach('id');
				break;
			case '2': // Alle Trainer mit noch ungesendeten E-Mails
				$objPlayers = \Database::getInstance()->prepare("SELECT pid FROM tl_lizenzverwaltung_mails WHERE sent_state = ? AND published = ?")
													  ->execute('', 1);
				$arrPlayers = is_array($arrPlayers) ? array_intersect($arrPlayers, $objPlayers->fetchEach('pid')) : $objPlayers->fetchEach('pid');
				break;
	
			default:
		}
	
		if (is_array($arrPlayers) && empty($arrPlayers)) 
		{
			$arrPlayers = array(0);
		}
	
		$log = print_r($arrPlayers, true);
		log_message($log, 'trainerlizenzen.log');
	
		$GLOBALS['TL_DCA']['tl_lizenzverwaltung']['list']['sorting']['root'] = $arrPlayers; 
	
	}

	/**
	 * Funktion getVerbandsmails
	 * =========================
	 * Liest die Mailadressen der Verbände ein
	 */
	public function getVerbandsmails()
	{
		// Datensatz einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE published = ?")
										  ->execute(1);

		// Auswerten
		if($result->numRows)
		{
			while($result->next()) 
			{
				$this->verbandsmail[$result->verband] = $result->email;
			}
		}

	}

	/**
	 * Funktion getNo
	 * ==============
	 * Konvertiert den Feldwert 0 auf ''
	 */
	public function getNo($varValue)
	{

		switch($varValue)
		{
			case '0':
				return '';
			default:
				return $varValue;
		}

	}

	/**
	 * Funktion setNo
	 * ==============
	 * Setzt den Feldwert auf 0 oder 1
	 */
	public function setNo($varValue)
	{

		switch($varValue)
		{
			case '1':
				return $varValue;
			default:
				return '0';
		}

	}

}