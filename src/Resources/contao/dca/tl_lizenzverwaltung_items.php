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

/**
 * Table tl_lizenzverwaltung
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_items'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_lizenzverwaltung',
		'ctable'                      => array('tl_lizenzverwaltung_mails'),
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'             => 'primary',
				'pid'            => 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('tstamp'),
			'headerFields'            => array('name', 'vorname', 'geburtstag', 'email', 'strasse', 'plz', 'ort'),
			'panelLayout'             => 'filter;sort,search,limit',
			'disableGrouping'         => false,
			'child_record_callback'   => array('tl_lizenzverwaltung_items', 'listLizenzen') 
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'emailbox' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['emailbox'],
				'href'                => 'table=tl_lizenzverwaltung_mails',
				'icon'                => 'bundles/contaolizenzverwaltung/images/email.png',
				'button_callback'     => array('tl_lizenzverwaltung_items', 'toggleEmail')
			), 
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('codex', 'addEnclosure','help'),
		'default'                     => 'verification,{dosb_legend},license_number_dosb,button_license,view_pdf,button_pdf,view_pdfcard,button_pdfcard;{marker_legend},marker;{verband_legend},verband;{lizenz_legend},lizenznummer,lizenz;{lizenzver_legend},erwerb,verlaengerungen;{lizenzbis_legend},gueltigkeit;{codex_legend},codex,help;{datum_legend},letzteAenderung,setHeute;{hinweise_legend:hide},addEnclosure,bemerkung;{published_legend},published'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'codex'                       => 'codex_date',
		'addEnclosure'                => 'enclosure,enclosureInfo',
		'help'                        => 'help_date'
	), 

	// Base fields in table tl_lizenzverwaltung
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'foreignKey'              => 'tl_lizenzverwaltung.id',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['tstamp'],
			'sorting'                 => true,
			'flag'                    => 6,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// Gibt Warnungen und Hinweise aus
		'verification' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['verification'],
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'getVerification'),
		),
		// DOSB-Lizenzstring, z.B. DSchB-T-C-0002146
		'license_number_dosb' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['license_number_dosb'],
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'getLizenznummer'),
			'exclude'                 => true,
			'flag'                    => 12,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		),
		// Lizenz erstellen/verlängern-Button und Infotext
		'button_license' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_license'],
			'exclude'                 => true,
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'getLizenzbutton'),
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		), 
		// PDF-Link Format DIN A4
		'view_pdf' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['view_pdf'],
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'viewPDF'),
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		),
		// Button zur PDF-Anforderung DIN A4 und Infotext
		'button_pdf' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdf'],
			'exclude'                 => true,
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'getLizenzPDF'),
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		), 
		// PDF-Link Format Card
		'view_pdfcard' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['view_pdfcard'],
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'viewPDFCard'),
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		),
		// Button zur PDF-Anforderung Format Card und Infotext
		'button_pdfcard' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdfcard'],
			'exclude'                 => true,
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'getLizenzPDFCard'),
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			)
		), 
		// DOSB-Lizenznummer, z.B. 3535 (korreliert mit der obigen Lizenz) 
		'lid' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['lid'],
			'exclude'                 => true,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// Unixzeit der letzten Lizenzerstellung/-verlängerung beim DOSB
		'dosb_tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_tstamp'],
			'flag'                    => 8,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// HTTP-Code der letzten Lizenzerstellung/-verlängerung beim DOSB
		'dosb_code' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_code'],
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
		),
		// Antwort der letzten Lizenzerstellung/-verlängerung beim DOSB
		'dosb_antwort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_antwort'],
			'sql'                     => "varchar(255) NOT NULL default ''",
		),
		// Unixzeit des letzten PDF-Abrufs beim DOSB
		'dosb_pdf_tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_pdf_tstamp'],
			'flag'                    => 8,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// HTTP-Code des letzten PDF-Abrufs beim DOSB
		'dosb_pdf_code' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_pdf_code'],
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
		),
		// Antwort des letzten PDF-Abrufs beim DOSB
		'dosb_pdf_antwort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_pdf_antwort'],
			'sql'                     => "varchar(255) NOT NULL default ''",
		),
		// Unixzeit des letzten PDF-Abrufs beim DOSB
		'dosb_pdfcard_tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_pdfcard_tstamp'],
			'flag'                    => 8,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// HTTP-Code des letzten PDF-Abrufs beim DOSB
		'dosb_pdfcard_code' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_pdfcard_code'],
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
		),
		// Antwort des letzten PDF-Abrufs beim DOSB
		'dosb_pdfcard_antwort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['dosb_pdfcard_antwort'],
			'sql'                     => "varchar(255) NOT NULL default ''",
		),
		'marker' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['marker'],
			'inputType'               => 'checkbox',
			'default'                 => false,
			'exclude'                 => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Verband
		'verband' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['verband'],
			'inputType'               => 'select',
			'exclude'                 => true,
			'flag'                    => 11,
			'sql'                     => "varchar(3) NOT NULL default ''",
			'options'                 => Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbaende(),
			'eval'                    => array
			(
				'mandatory'           => true,
				'chosen'              => true,
				'tl_class'            => 'w50'
			)
		),
		// Lizenznummer
		'lizenznummer' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['lizenznummer'],
			'inputType'               => 'text',
			'default'                 => 'B.38',
			'exclude'                 => true,
			'sql'                     => "varchar(255) NOT NULL default ''",
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'doNotCopy'           => true,
				'mandatory'           => false,
			)
		),
		// Lizenz
		'lizenz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['lizenz'],
			'inputType'               => 'select',
			'exclude'                 => true,
			'sorting'                 => true,
			'options'                 => Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getLizenzen(),
			'eval'                    => array
			(
				'chosen'              => true,
				'tl_class'            => 'w50',
				'doNotCopy'           => true,
				'mandatory'           => true,
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		// Datum des Lizenzerwerbs
		'erwerb' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['erwerb'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 8,
			'explanation'             => 'lizenzverwaltung_erwerb', 
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'datepicker'          => true,
				'helpwizard'          => true,
				'tl_class'            => 'w50 wizard',
				'doNotCopy'           => true
			),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		'verlaengerungen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['verlaengerungen'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'columnFields'        => array
				(
					'datum' => array
					(
						'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['verlaengerung_datum'],
						'exclude'                 => true,
						'inputType'               => 'text',
						'eval'                    => array
						(
							'tl_class'            => 'wizard',
							'rgxp'                => 'date',
							'datepicker'          => true,
							'maxlength'           => 10,
							'style'               => 'width:92%'
						),
					),
				)
			),
			'sql'                     => "blob NULL"
		),
		// Datum der Lizenzgültigkeit
		'gueltigkeit' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['gueltigkeit'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 8,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'datepicker'          => true,
				'tl_class'            => 'w50 wizard',
				'doNotCopy'           => true
			),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		// Ehrencodex anerkannt
		'codex' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['codex'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'explanation'             => 'lizenzverwaltung_codex', 
			'exclude'                 => true,
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'w50',
				'helpwizard'          => true,
				'isBoolean'           => true,
				'submitOnChange'      => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Datum Ehrencodex
		'codex_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['codex_date'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 8,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'datepicker'          => true,
				'tl_class'            => 'w50 wizard',
				'doNotCopy'           => true
			),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		// Erste-Hilfe-Ausbildung absolviert
		'help' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['help'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'exclude'                 => true,
			'explanation'             => 'lizenzverwaltung_codex', 
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'w50 clr',
				'helpwizard'          => true,
				'isBoolean'           => true,
				'submitOnChange'      => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Datum der Erste-Hilfe-Ausbildung
		'help_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['help_date'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 8,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'datepicker'          => true,
				'tl_class'            => 'w50 wizard',
				'doNotCopy'           => true
			),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		// Datum der letzten Änderung
		'letzteAenderung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['letzteAenderung'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 8,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'datepicker'          => true,
				'tl_class'            => 'w50 wizard',
				'doNotCopy'           => true
			),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		// Heutiges Datum bei letzteAenderung setzen
		'setHeute' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['setHeute'],
			'exclude'                 => true,
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'setHeute')
		), 
		'addEnclosure' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['addEnclosure'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['enclosure'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			//'load_callback'           => array(array('tl_lizenzverwaltung_items', 'viewFiles')),
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['enclosureInfo'],
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'viewEnclosureInfo'),
		),
		'bemerkung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['bemerkung'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'eval'                    => array('rte' => 'tinyMCE', 'cols' => 80,'rows' => 10),
			'sql'                     => "text NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['published'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'exclude'                 => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
	),
);

class tl_lizenzverwaltung_items extends \Backend
{
	
	var $verbandsmail = array();

	public function toggleEmail($row, $href, $label, $title, $icon, $attributes)
	{
		$this->import('BackendUser', 'User');

		$href .= '&amp;id='.$row['id'];

		$verband_email = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbandMail($row['verband']);
		$person_email = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getPersonMail($row['pid']);

		if($person_email && $verband_email)
		{
			$icon = 'bundles/contaolizenzverwaltung/images/email.png';
		}
		elseif($person_email || $verband_email)
		{
			$icon = 'bundles/contaolizenzverwaltung/images/email_gelb.png';
		}
		else
		{
			$icon = 'bundles/contaolizenzverwaltung/images/email_grau.png';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
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
		
		$this->createInitialVersion('tl_lizenzverwaltung_items', $intId);
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_lizenzverwaltung_items']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_lizenzverwaltung_items']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}
		
		// Update the database
		$this->Database->prepare("UPDATE tl_lizenzverwaltung SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
					   ->execute($intId);
		$this->createNewVersion('tl_lizenzverwaltung_items', $intId);
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
			$args[$x] = \Schachbulle\ContaoHelperBundle\Classes\Helper::getDate($args[$x]);
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
		<div class="w50 dosb_margin">
		<div class="tl_text" style="border:0">'.$status.'</div>
		</div>';
		
		return $string;
	}

	public function getLizenzbutton(DataContainer $dc)
	{
		// Link generieren
		$link = str_replace('&amp;act=edit', '', \Controller::addToUrl('key=getLizenz&rt='.REQUEST_TOKEN)); // key hinzufügen | edit löschen

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_tstamp).' ('.$dc->activeRecord->dosb_code.' '.$dc->activeRecord->dosb_antwort.')';
			return '
			<div class="w50 dosb_margin">
			<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_license'][0].'</a></div>
			<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$antwort.'</p>
			</div>';
		}
		else
		{
			return '
			<div class="w50 dosb_margin">
			<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_license'][0].'</a></div>
			</div>';
		}

	}
	

	/**
	 * Button zum PDF-Abruf im DIN-A4-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function getLizenzPDF(DataContainer $dc)
	{
		// Link generieren
		$link = str_replace('&amp;act=edit', '', \Controller::addToUrl('key=getLizenzPDF&rt='.REQUEST_TOKEN)); // key hinzufügen | edit löschen
		
		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_pdf_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_pdf_tstamp).' ('.$dc->activeRecord->dosb_pdf_code.' '.$dc->activeRecord->dosb_pdf_antwort.')';
		}
		else $antwort = '';

		if($dc->activeRecord->license_number_dosb)
		{
			if($antwort)
			{
				return '
				<div class="w50 dosb_margin">
				<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdf'][0].'</a></div>
				<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$antwort.'</p>
				</div>';
			}
			else
			{
				return '
				<div class="w50 dosb_margin">
				<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdf'][0].'</a></div>
				</div>';
			}
		}
		else return '<div class="w50 dosb_margin"></div>';
			
	}

	/**
	 * Button zum PDF-Abruf im Karten-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function getLizenzPDFCard(DataContainer $dc)
	{
		// Link generieren
		$link = str_replace('&amp;act=edit', '', \Controller::addToUrl('key=getLizenzPDFCard&rt='.REQUEST_TOKEN)); // key hinzufügen | edit löschen

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_pdfcard_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_pdfcard_tstamp).' ('.$dc->activeRecord->dosb_pdfcard_code.' '.$dc->activeRecord->dosb_pdfcard_antwort.')';
		}
		else $antwort = '';

		if($dc->activeRecord->license_number_dosb)
		{
			if($antwort)
			{
				return '
				<div class="w50 dosb_margin">
				<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdfcard'][0].'</a></div>
				<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$antwort.'</p>
				</div>';
			}
			else
			{
				return '
				<div class="w50 dosb_margin">
				<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdfcard'][0].'</a></div>
				</div>';
			}
		}
		else return '<div class="w50 dosb_margin"></div>';
			
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
		$link .= '?do=lizenzverwaltung&amp;table=tl_lizenzverwaltung_items&amp;key=getLizenz&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;

		// Letzter Lizenzabruf und Rückgabecode
		if($dc->activeRecord->dosb_tstamp)
		{
			$antwort = 'Letzter Abruf: '.date('d.m.Y H:i:s', $dc->activeRecord->dosb_tstamp).' ('.$dc->activeRecord->dosb_code.' '.$dc->activeRecord->dosb_antwort.')';
		}
		else $antwort = '';
		
		$string = '
<div class="w50 widget" style="display:none">
	<a href="#" onclick="AjaxRequest.toggleSubpalette(this, \'sub_login\', \'login\')" onfocus="Backend.getScrollOffset()" class="dosb_button_mini">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['setHeute'][0].'</a>
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['setHeute'][1].'</p>
</div>'; 
		
		return $string;
	}

	/**
	 * List records
	 *
	 * @param array $arrRow
	 *
	 * @return string
	 */
	public function listLizenzen($row)
	{
		// Farbliche Hervorhebung der Gültigkeit
		if($row['gueltigkeit'] < time()) $temp = '<span style="color: red;">';
		else $temp = '<span style="color: green;">';

		$temp .= $row['marker'] ? '<img src="bundles/contaolizenzverwaltung/images/marker.png" title="Lizenz ist markiert"> ' : '';
		$temp .= '<b>'.$row['lizenz'].'</b> ';
		$temp .= date('d.m.Y', $row['gueltigkeit']).' ';
		$temp .= '- '.\Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerband($row['verband']).' ';
		if($row['license_number_dosb'])
		{
			$temp .= '(DOSB-Lizenz <i>'.$row['license_number_dosb'].'</i> ';
			$temp .= 'abgerufen am '.date('d.m.Y H:i', $row['dosb_tstamp']).' - Code: '.$row['dosb_code'].' '.$row['dosb_antwort'].')</span>';
		}
		else
		{
			$temp .= '(noch nicht beim DOSB gemeldet)</span>';
		}
		return $temp;
		
//		return '
//<div class="cte_type ' . (($arrRow['sent_state'] && $arrRow['sent_date']) ? 'published' : 'unpublished') . '"><strong>' . $arrRow['subject'] . '</strong> - ' . (($arrRow['sent_state'] && $arrRow['sent_date']) ? 'Versendet am '.Date::parse(Config::get('datimFormat'), $arrRow['sent_date']) : 'Nicht versendet'). '</div>'; 

	}
	
	public function getVerification(DataContainer $dc)
	{

		// ----------------------------------------------------------------
		// GÜLTIGKEIT DER LIZENZ
		// ----------------------------------------------------------------
		// Letztes Verlängerungsdatum ermitteln
		$verlaengerung = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerlaengerung($dc->activeRecord->erwerb, $dc->activeRecord->verlaengerungen);

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

		// Lizenz- und Personen-Datensatz einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                  ->execute($dc->activeRecord->id);

		if(!$result->email && $dc->activeRecord->tstamp)
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

		//log_message(date('d.m.Y', $value), 'lizenzverwaltung_quartal.log');
		//log_message($quartal, 'lizenzverwaltung_quartal.log');
		
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
		$link .= '?do=lizenzverwaltung&amp;table=tl_lizenzverwaltung_items&amp;key=getLizenz&amp;id=' . $dc->activeRecord->id . '&amp;rt=' . REQUEST_TOKEN;

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
	<h3><label for="ctrl_enclosureInfo">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['enclosureInfo'][0].'</label></h3>
	'.$antwort.'
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['enclosureInfo'][1].'</p>
</div>'; 
		
		return $string;
	}

	/**
	 * Link zum PDF im DIN-A4-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function viewPDF(\DataContainer $dc)
	{
		$lizenzordner = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner']);

		// Links zum PDF generieren
		$pdf_server = TL_ROOT.'/'.$lizenzordner->path.'/'.$dc->activeRecord->license_number_dosb.'.pdf';
		$pdf_download = $lizenzordner->path.'/'.$dc->activeRecord->license_number_dosb.'.pdf';
		
		// Lizenzstatus
		if($dc->activeRecord->license_number_dosb && file_exists($pdf_server))
		{
			$pdf_datum = date('d.m.Y H:i:s', filemtime($pdf_server));
			$status = '<a href="'.$pdf_download.'" target="_blank" title="Zeigt die auf dem DSB-Server gespeicherte Lizenzurkunde an." class="dosb_button_mini">PDF DIN A4 anzeigen</a>';
			$info = 'Datum: '.$pdf_datum;
		}
		else
		{
			$status = 'Kein PDF DIN A4 vorhanden';
		}
		
		if($dc->activeRecord->license_number_dosb)
		{
			return '
			<div class="w50 dosb_margin">
			<div class="tl_text" style="border:0;">'.$status.'</div>
			<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$info.'</p>
			</div>';
		}
		else return '<div class="w50 dosb_margin"></div>';
	}

	/**
	 * Link zum PDF im Karten-Format anzeigen
	 * @param DataContainer $dc
	 *
	 * @return string HTML-Code
	 */
	public function viewPDFCard(\DataContainer $dc)
	{
		$lizenzordner = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner']);

		// Links zum PDF generieren
		$pdf_server = TL_ROOT.'/'.$lizenzordner->path.'/'.$dc->activeRecord->license_number_dosb.'-card.pdf';
		$pdf_download = $lizenzordner->path.'/'.$dc->activeRecord->license_number_dosb.'-card.pdf';

		// Lizenzstatus
		if($dc->activeRecord->license_number_dosb && file_exists($pdf_server))
		{
			$pdf_datum = date('d.m.Y H:i:s', filemtime($pdf_server));
			$status = '<a href="'.$pdf_download.'" target="_blank" title="Zeigt die auf dem DSB-Server gespeicherte Lizenzurkunde im Format Card an." class="dosb_button_mini">PDF Karte anzeigen</a>';
			$info = 'Datum: '.$pdf_datum;
		}
		else
		{
			$status = 'Kein PDF Card vorhanden';
		}
		
		if($dc->activeRecord->license_number_dosb)
		{
			return '
			<div class="w50 dosb_margin">
			<div class="tl_text" style="border:0;">'.$status.'</div>
			<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$info.'</p>
			</div>';
		}
		else return '<div class="w50 dosb_margin"></div>';
	}


}