<?php

/**
 * Tabelle tl_lizenzverwaltung_referenten
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_referenten'] = array
(

	// Konfiguration
	'config' => array
	(
		'dataContainer'               => 'Table',
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
			)
		),
	),

	// Datensätze auflisten
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('nachname ASC','vorname ASC'),
			'flag'                    => 11,
			'panelLayout'             => 'filter;sort,search,limit',
		),
		'label' => array
		(
			// Das Feld aktiv wird vom label_callback überschrieben
			'fields'                  => array('nachname','vorname','verband','funktion'),
			'showColumns'             => true,
			'format'                  => '%s',
		),
		'global_operations' => array
		(
			'referenten' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['personen'],
				'href'                => 'table=tl_lizenzverwaltung',
				'icon'                => 'bundles/contaolizenzverwaltung/images/personen.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_lizenzverwaltung_referenten', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif',
				'attributes'          => 'style="margin-right:3px"'
			),
		)
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{verband_legend},verband,funktion;{person_legend},nachname,vorname,titel;{email_legend},email;{adresse_legend:hide},plz,ort,strasse;{telefon_legend:hide},telefon1,telefon2,telefax1,telefax2;{info_legend:hide},info;{sent_legend},sent_info,sent_date;{publish_legend},published'
	),

	// Felder
	'fields' => array
	(
		'id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['id'],
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['tstamp'],
			'sorting'                 => true,
			'flag'                    => 6,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		// Verband
		'verband' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['verband'],
			'inputType'               => 'select',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'filter'                  => true,
			'sql'                     => "varchar(1) NOT NULL default ''",
			'options'                 => \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbaende(),
			'eval'                    => array
			(
				'mandatory'           => true,
				'chosen'              => true,
				'tl_class'            => 'w50',
				'includeBlankOption'  => true
			)
		),
		// Funktion
		'funktion' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['funktion'],
			'inputType'               => 'select',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'filter'                  => true,
			'sql'                     => "varchar(4) NOT NULL default ''",
			'options'                 => array
			(
				'-'                   => '-',
				'ausb'                => 'Ausbildungs-Referent',
				'gefu'                => 'Geschäftsführer',
				'prae'                => 'Präsident',
				'vize'                => 'Vizepräsident',
				'kass'                => 'Schatzmeister',
				'scri'                => 'Schriftführer',
				'spie'                => 'Spielleiter',
				'jugd'                => 'Jugend-Referent',
				'wert'                => 'Wertungs-Referent',
				'dver'                => 'DV-Referent',
				'prss'                => 'Presse-Referent',
				'brei'                => 'Breitensport-Referent',
				'seni'                => 'Senioren-Referent',
				'frau'                => 'Frauen-Referent',
			),
			'eval'                    => array
			(
				'mandatory'           => false,
				'chosen'              => true,
				'tl_class'            => 'w50'
			)
		),
		'nachname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['nachname'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'maxlength'           => 255, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'vorname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['vorname'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'maxlength'           => 255, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'titel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['titel'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 1,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'plz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['plz'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'ort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['ort'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'strasse' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['strasse'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 255,
				'tl_class'            => 'w50 clr'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'telefon1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['telefon1'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefon2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['telefon2'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefax1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['telefax1'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefax2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['telefax2'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['email'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true, 
				'maxlength'           => 255, 
				'tl_class'            => 'w50',
				'rgxp'                => 'email'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'info' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['info'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'tl_class'=>'long'),
			'sql'                     => "text NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'doNotCopy'           => true,
				'isBoolean'           => true,
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Quartalsbericht: Aktuelle Lizenzen versenden
		'sent_info' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['sent_info'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'doNotCopy'           => true,
				'isBoolean'           => true,
				'tl_class'            => 'w50',
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Quartalsbericht: Datum des letzten Lizenzversands
		'sent_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_referenten']['sent_date'],
			'input_field_callback'    => array('tl_lizenzverwaltung_referenten', 'getLizenzversand'),
			'flag'                    => 8,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
	)
);

/**
 * Class tl_member_aktivicon
 */
class tl_lizenzverwaltung_referenten extends Backend
{

	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		$this->import('BackendUser', 'User');

		if (strlen($this->Input->get('tid')))
		{
			$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_lizenzverwaltung_referenten::published', 'alexf'))
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
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_lizenzverwaltung_referenten::published', 'alexf'))
		{
			$this->log('Kein Zugriffsrecht für Aktivierung Datensatz ID "'.$intId.'"', 'tl_lizenzverwaltung_referenten toggleVisibility', TL_ERROR);
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
		
		$this->createInitialVersion('tl_lizenzverwaltung_referenten', $intId);
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_lizenzverwaltung_referenten']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_lizenzverwaltung_referenten']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}
		
		// Update the database
		$this->Database->prepare("UPDATE tl_lizenzverwaltung_referenten SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
					   ->execute($intId);
		$this->createNewVersion('tl_lizenzverwaltung_referenten', $intId);
	}

	public function getLizenzversand(DataContainer $dc)
	{

		// Letzter Lizenzversand
		if($dc->activeRecord->sent_date)
		{
			$content = 'Letzter Versand: '.date('d.m.Y H:i:s', $dc->activeRecord->sent_date);
		}
		else $content = 'Letzter Versand: -';

		return '
<div class="w50">
	<div class="tl_checkbox_single_container">'.$content.'</div>
</div>'; 

	}

}
