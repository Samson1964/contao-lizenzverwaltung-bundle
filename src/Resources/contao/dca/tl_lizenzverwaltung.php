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

$GLOBALS['TL_CSS'][] = 'bundles/contaolizenzverwaltung/css/default.css';

/**
 * Table tl_lizenzverwaltung
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_lizenzverwaltung_items'),
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_lizenzverwaltung', 'applyAdvancedFilter'),
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
				'icon'                => 'bundles/contaolizenzverwaltung/images/referenten.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'exportDOSB' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportDOSB'],
				'href'                => 'key=exportDOSB',
				'icon'                => 'bundles/contaolizenzverwaltung/images/export.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['export'],
				'href'                => 'key=export',
				'icon'                => 'bundles/contaolizenzverwaltung/images/exportCSV.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'exportXLS' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportXLS'],
				'href'                => 'key=exportXLS',
				'icon'                => 'bundles/contaolizenzverwaltung/images/exportEXCEL.gif',
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
			'editHeader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['editHeader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
			),
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['edit'],
				'href'                => 'table=tl_lizenzverwaltung_items',
				'icon'                => 'bundles/contaolizenzverwaltung/images/icon.png'
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
		'default'                     => '{name_legend},vorname,name,titel,geburtstag,geschlecht;{adresse_legend},strasse,plz,ort,email,telefon;{hinweise_legend:hide},addEnclosure,bemerkung;{published_legend},published'
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
			'sorting'                 => true,
			'flag'                    => 8,
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
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => true,
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
			'search'                  => false,
			'filter'                  => false,
			'sorting'                 => false,
			'flag'                    => 8,
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
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => true,
			'flag'                    => 12,
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
			'sorting'                 => false,
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
			'filter'                  => false,
			'explanation'             => 'trainerlizenzen_plz',
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
			'sorting'                 => false,
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
			'eval'                    => array('rte' => 'tinyMCE', 'cols' => 80,'rows' => 10),
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

	public function generateAdvancedFilter(DataContainer $dc)
	{

		if(\Input::get('id') > 0) return '';

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
					'1' => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_active_licenses'],
					'2' => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_inactive_licenses'],
					'3' => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_unsentmails'],
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
			case '1': // Alle Personen mit gültigen Lizenzen
				$objPlayers = \Database::getInstance()->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ?")
				                                      ->execute(time(), 1);
				$arrPlayers = is_array($arrPlayers) ? array_intersect($arrPlayers, $objPlayers->fetchEach('id')) : $objPlayers->fetchEach('id');
				break;

			case '2': // Alle Personen mit ungültigen Lizenzen
				$objPlayers = \Database::getInstance()->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit < ? AND tl_lizenzverwaltung_items.published = ?")
				                                      ->execute(time(), 1);
				$arrPlayers = is_array($arrPlayers) ? array_intersect($arrPlayers, $objPlayers->fetchEach('id')) : $objPlayers->fetchEach('id');
				break;

			case '3': // Alle Personen mit ungesendeten E-Mails
				$objPlayers = \Database::getInstance()->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung.id = tl_lizenzverwaltung_items.pid LEFT JOIN tl_lizenzverwaltung_mails ON tl_lizenzverwaltung_items.id = tl_lizenzverwaltung_mails.pid WHERE tl_lizenzverwaltung_mails.sent_state = ? AND tl_lizenzverwaltung_items.published = ?")
				                                      ->execute('', 1);
				$arrPlayers = is_array($arrPlayers) ? array_intersect($arrPlayers, $objPlayers->fetchEach('id')) : $objPlayers->fetchEach('id');
				break;

			default:

		}

		if (is_array($arrPlayers) && empty($arrPlayers))
		{
			$arrPlayers = array(0);
		}

		$log = print_r($arrPlayers, true);
		log_message($log, 'lizenzverwaltung.log');

		$GLOBALS['TL_DCA']['tl_lizenzverwaltung']['list']['sorting']['root'] = $arrPlayers;

	}

}