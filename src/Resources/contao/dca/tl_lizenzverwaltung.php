<?php

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoHelperBundle\Classes\Helper as ContaoHelper;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper;

/**
 * Tabelle tl_lizenzverwaltung
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung'] = array
(
	// Config
	'config' => array
	(
		// Der Kurzname 'Table' gibt es unter Contao 5 nicht mehr, der FQCN in beiden
		'dataContainer'               => DC_Table::class,
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
				'name'           => 'index',
				'alias'          => 'index'
			)
		),
		'onsubmit_callback' => array
		(
			array('tl_lizenzverwaltung', 'generateAlias')
		),
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
			'fields'                  => array('name', 'vorname', 'geburtstag', 'email', 'lizenzen', 'verbaende'),
			'showColumns'             => true,
			'label_callback'          => array('tl_lizenzverwaltung', 'viewLabels'),
		),
		'global_operations' => array
		(
			'verbaende' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['verbaende_all'],
				'href'                => 'table=tl_lizenzverwaltung_verbaende',
				'icon'                => 'bundles/contaolizenzverwaltung/images/verband.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
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
			'exportXLS' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportXLS'],
				'href'                => 'key=exportXLS',
				'icon'                => 'bundles/contaolizenzverwaltung/images/exportEXCEL.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'deleteMarker' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['deleteMarker'],
				'href'                => 'key=deleteMarker',
				'icon'                => 'bundles/contaolizenzverwaltung/images/marker_delete.png',
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_lizenzverwaltung']['deleteMarker_confirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
			),
			'templates' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['templates'],
				'href'                => 'table=tl_lizenzverwaltung_templates',
				'icon'                => 'bundles/contaolizenzverwaltung/images/templates.png',
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
				'attributes'          => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
			(
				'label'                => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['toggle'],
				'attributes'           => 'onclick="Backend.getScrollOffset()"',
				'haste_ajax_operation' => array
				(
					'field'            => 'published',
					'options'          => array
					(
						array('value' => '', 'icon' => 'invisible.svg'),
						array('value' => '1', 'icon' => 'visible.svg'),
					),
				),
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
		// Alias aus Vorname und Nachname
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['alias'],
			'inputType'               => 'text',
			'search'                  => true,
			'sql'                     => "varchar(255) NOT NULL default ''",
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
			'explanation'             => 'lizenzverwaltung_strasse',
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
			'explanation'             => 'lizenzverwaltung_plz',
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
			'explanation'             => 'lizenzverwaltung_strasse',
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
		'lizenzen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['lizenzen'],
		),
		'verbaende' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['verbaende'],
		),
	),
);


/**
 * Rückrufe des Data Containers tl_lizenzverwaltung.
 */
class tl_lizenzverwaltung extends Backend
{
	/**
	 * Erzeugt das Objekt.
	 *
	 * Der öffentliche Konstruktor ist Pflicht: Unter Contao 4.13 ist
	 * `Backend::__construct()` nur protected, die Klasse ließe sich dort sonst
	 * von außerhalb der Contao-Klassenhierarchie nicht erzeugen.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Baut den Spezialfilter über der Lizenzliste.
	 *
	 * Der Filter erscheint als eigenes Feld in der Filterleiste
	 * (`panelLayout` => 'myfilter;...') und erlaubt Auswertungen, die sich mit
	 * den Standardfiltern nicht abbilden lassen — etwa "alle Personen mit
	 * abgelaufenen Lizenzen", was eine Bedingung auf der Kindtabelle verlangt.
	 *
	 * @param DataContainer $dc Der Data Container der Übersicht
	 *
	 * @return string Der HTML-Code des Filterfeldes. In der Detailansicht einer
	 *                Person (Parameter id gesetzt) bleibt er leer, weil dort
	 *                nicht gefiltert wird.
	 */
	public function generateAdvancedFilter(DataContainer $dc): string
	{
		if (Input::get('id') > 0)
		{
			return '';
		}

		$gewaehlt = $this->getFilterValue();

		$optionen = array
		(
			'1'   => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_active_licenses'] ?? 'Gültige Lizenzen',
			'2'   => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_inactive_licenses'] ?? 'Ungültige Lizenzen',
			'3'   => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_marked_licenses'] ?? 'Markierte Lizenzen',
			'4'   => $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_unsentmails'] ?? 'Ungesendete E-Mails',
		);

		// Je Verband ein Eintrag; das Kennzeichen steht hinter dem "V"
		foreach (array('S', '1', '2', '3', 'D', 'B', '4', '5', 'E', '7', '6', '8', '9', 'F', 'G', 'A', 'H', 'C') as $kennzeichen)
		{
			$optionen['V'.$kennzeichen] = $GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_'.$kennzeichen] ?? Helper::getVerband($kennzeichen);
		}

		$strOptions = '
  <option value="tli_filter">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_extended'] ?? 'Spezialfilter').'</option>
  <option value="tli_filter">---</option>'."\n";

		foreach ($optionen as $k => $v)
		{
			$strOptions .= '  <option value="'.StringUtil::specialchars((string) $k).'"'.($gewaehlt === (string) $k ? ' selected' : '').'>'.StringUtil::specialchars((string) $v).'</option>'."\n";
		}

		return '
<div class="tl_filter tli_filter tl_subpanel">
<strong>'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter'] ?? 'Filter').':</strong> '."\n"
.'<select name="tli_filter" id="tli_filter" class="tl_select'.($gewaehlt ? ' active' : '').'">
'.$strOptions.'
</select>'."\n".'</div>';
	}

	/**
	 * Wendet den Spezialfilter auf die Übersicht an.
	 *
	 * Der gewählte Wert wird zunächst aus dem abgeschickten Formular in den
	 * Sitzungsspeicher übernommen (ein erneutes Absenden desselben Wertes
	 * setzt den Filter zurück, wie bei Contaos eigenen Filtern). Anschließend
	 * werden die passenden Datensatz-IDs ermittelt und als `root` gesetzt —
	 * das ist der einzige Weg, die Übersicht einer Elterntabelle über eine
	 * Bedingung auf der Kindtabelle einzuschränken.
	 *
	 * @return void Trifft der Filter auf nichts zu, wird `root` auf array(0)
	 *              gesetzt, damit die Liste leer bleibt statt alles zu zeigen
	 */
	public function applyAdvancedFilter(): void
	{
		$bag = Helper::getBackendSessionBag();

		if (null === $bag)
		{
			return;
		}

		$session = $bag->get('filter') ?? array();

		foreach (array_keys($_POST) as $k)
		{
			if (!\is_string($k) || 'tli_' !== substr($k, 0, 4))
			{
				continue;
			}

			// Wird der Name des Feldes als Wert geschickt, ist "---" gewählt: Filter aus
			if ($k === Input::post($k))
			{
				unset($session['tl_lizenzverwaltungFilter'][$k]);
			}
			else
			{
				$session['tl_lizenzverwaltungFilter'][$k] = Input::post($k);
			}

			$bag->set('filter', $session);
		}

		$filter = $session['tl_lizenzverwaltungFilter']['tli_filter'] ?? null;

		if (Input::get('id') > 0 || !$filter)
		{
			return;
		}

		$arrPlayers = $this->findPlayers((string) $filter);

		if (null === $arrPlayers)
		{
			return;
		}

		$GLOBALS['TL_DCA']['tl_lizenzverwaltung']['list']['sorting']['root'] = $arrPlayers ?: array(0);
	}

	/**
	 * Ermittelt die Personen-IDs zu einem Spezialfilterwert.
	 *
	 * @param string $filter Der gewählte Wert, etwa "1" oder "V3"
	 *
	 * @return array<int,string>|null Die IDs, oder null bei einem unbekannten
	 *                                Wert — dann bleibt die Übersicht ungefiltert
	 */
	private function findPlayers(string $filter): ?array
	{
		$db = Database::getInstance();

		switch ($filter)
		{
			case '1': // Alle Personen mit gültigen Lizenzen
				return $db->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ?")
				          ->execute(time(), 1)
				          ->fetchEach('id');

			case '2': // Alle Personen mit ungültigen Lizenzen
				return $db->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit < ? AND tl_lizenzverwaltung_items.published = ?")
				          ->execute(time(), 1)
				          ->fetchEach('id');

			case '3': // Alle Personen mit markierten Lizenzen
				return $db->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.marker = ?")
				          ->execute(1)
				          ->fetchEach('id');

			case '4': // Alle Personen mit ungesendeten E-Mails
				return $db->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung.id = tl_lizenzverwaltung_items.pid LEFT JOIN tl_lizenzverwaltung_mails ON tl_lizenzverwaltung_items.id = tl_lizenzverwaltung_mails.pid WHERE tl_lizenzverwaltung_mails.sent_state = ? AND tl_lizenzverwaltung_items.published = ?")
				          ->execute('', 1)
				          ->fetchEach('id');
		}

		// Verbandsfilter: "V" gefolgt vom Verbandskennzeichen
		if ('V' === substr($filter, 0, 1) && 2 === \strlen($filter))
		{
			return $db->prepare("SELECT tl_lizenzverwaltung.id FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.verband = ?")
			          ->execute(substr($filter, 1, 1))
			          ->fetchEach('id');
		}

		return null;
	}

	/**
	 * Liest den gewählten Wert des Spezialfilters aus der Sitzung.
	 *
	 * @return string Der Wert, oder eine leere Zeichenkette wenn nicht gefiltert wird
	 */
	private function getFilterValue(): string
	{
		$bag = Helper::getBackendSessionBag();

		if (null === $bag)
		{
			return '';
		}

		return (string) (($bag->get('filter') ?? array())['tl_lizenzverwaltungFilter']['tli_filter'] ?? '');
	}

	/**
	 * Zeigt die dem Datensatz angehängten Dateien an.
	 *
	 * @param DataContainer $dc Der Data Container; ausgewertet wird das Feld enclosure
	 *
	 * @return string Der HTML-Code des Anzeigefeldes; ohne Anhänge nur die
	 *                Beschriftung samt Hilfetext
	 */
	public function viewEnclosureInfo(DataContainer $dc): string
	{
		$antwort = $this->renderEnclosures($dc);

		return '
<div class="clr widget">
	<h3><label for="ctrl_enclosureInfo">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'][0] ?? 'Angehängte Dateien').'</label></h3>
	'.$antwort.'
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'][1] ?? '').'</p>
</div>';
	}

	/**
	 * Baut die Liste der angehängten Dateien mit Vorschaubildern.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code der Liste, oder eine leere Zeichenkette wenn
	 *                keine Dateien angehängt sind
	 */
	private function renderEnclosures(DataContainer $dc): string
	{
		$record = Database::getInstance()->prepare("SELECT enclosure FROM tl_lizenzverwaltung WHERE id = ?")
		                                 ->limit(1)
		                                 ->execute($dc->id);

		$dateien = StringUtil::deserialize($record->enclosure, true);

		if (!$dateien)
		{
			return '';
		}

		$content = '<ul>';

		foreach ($dateien as $item)
		{
			$objFile = FilesModel::findByUuid($item);

			if (null === $objFile)
			{
				continue;
			}

			$arrMeta = StringUtil::deserialize($objFile->meta, true);

			$content .= '<li style="clear:both;">';

			if (\in_array($objFile->extension, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true))
			{
				$vorschau = $this->getThumbnail($objFile->path);

				if ('' !== $vorschau)
				{
					$content .= '<a href="'.StringUtil::specialchars($objFile->path).'"><img src="'.StringUtil::specialchars($vorschau).'" alt="" style="float:left; margin-right:5px; margin-bottom:5px;"></a> ';
				}
			}

			$content .= StringUtil::specialchars($objFile->path).'<br>';
			$content .= '<i>'.StringUtil::specialchars((string) ($arrMeta['de']['title'] ?? '')).'</i>';
			$content .= '</li>';
		}

		return $content.'<li style="clear:both;"></li></ul>';
	}

	/**
	 * Erzeugt ein Vorschaubild zu einer Datei.
	 *
	 * `Image::get()` gibt es unter Contao 5 nicht mehr; der Bilddienst
	 * `contao.image.factory` heißt dagegen in beiden Fassungen gleich.
	 *
	 * @param string $path Pfad der Datei, relativ zum Projektverzeichnis
	 *
	 * @return string Die Adresse des Vorschaubildes, oder eine leere
	 *                Zeichenkette wenn es sich nicht erzeugen ließ
	 */
	private function getThumbnail(string $path): string
	{
		try
		{
			$projectDir = Helper::getProjectDir();

			return System::getContainer()->get('contao.image.factory')
			             ->create($projectDir.'/'.$path, array(80, 80, 'crop'))
			             ->getUrl($projectDir);
		}
		catch (\Throwable $e)
		{
			return '';
		}
	}

	/**
	 * Ergänzt die Übersichtszeile um Lizenzen und Verbände der Person.
	 *
	 * Die Felder `lizenzen` und `verbaende` haben keine Datenbankspalte; sie
	 * stehen nur als Platzhalter in `list.label.fields` und werden hier
	 * gefüllt.
	 *
	 * @param array<string,mixed> $row   Der Datensatz der Person
	 * @param string              $label Der bereits erzeugte Beschriftungstext
	 * @param DataContainer       $dc    Der Data Container
	 * @param array<int,string>   $args  Die sichtbaren Spaltenwerte; Index 4 ist
	 *                                   das Feld lizenzen, Index 5 das Feld verbaende
	 *
	 * @return array<int,string> Die ergänzten Spaltenwerte
	 */
	public function viewLabels($row, $label, DataContainer $dc, $args)
	{
		$objLizenzen = Database::getInstance()->prepare("SELECT lizenz, marker, verband, gueltigkeit FROM tl_lizenzverwaltung_items WHERE pid = ?")
		                                      ->execute($row['id']);

		$lizenzen  = array();
		$verbaende = array();

		while ($objLizenzen->next())
		{
			$gueltig = date('d.m.Y', (int) $objLizenzen->gueltigkeit);

			// Abgelaufene Lizenzen rot, gültige grün
			$str = $objLizenzen->gueltigkeit < time()
				? '<span style="color: red;" title="abgelaufen am '.$gueltig.'">'
				: '<span style="color: green;" title="gültig bis '.$gueltig.'">';

			$marker = $objLizenzen->marker ? '<img src="bundles/contaolizenzverwaltung/images/marker.png" alt="" title="Lizenz ist markiert">' : '';

			$lizenzen[]  = $str.StringUtil::specialchars((string) $objLizenzen->lizenz).$marker.'</span>';
			$verbaende[] = Helper::getVerband((string) $objLizenzen->verband);
		}

		if ($lizenzen)
		{
			$args[4] = implode(', ', $lizenzen);
			$args[5] = implode(', ', array_unique($verbaende));
		}

		return $args;
	}

	/**
	 * Erzeugt beim Speichern den Alias aus Nachname und Vorname.
	 *
	 * Der Alias wird per eigenem UPDATE geschrieben, weil er kein Feld der
	 * Palette ist. Der `onsubmit_callback` ist dafür die richtige Stelle: Er
	 * läuft in beiden Contao-Fassungen nach dem Schreiben der Palettenfelder,
	 * ein UPDATE aus einem `save_callback` heraus würde unter Contao 5 vom
	 * gesammelten UPDATE des Data Containers überschrieben.
	 *
	 * @param DataContainer $dc Der Data Container mit der ID des Datensatzes
	 *
	 * @return void Ohne Datensatz-ID passiert nichts
	 */
	public function generateAlias(DataContainer $dc): void
	{
		if (!$dc->id)
		{
			return;
		}

		$record = Database::getInstance()->prepare("SELECT name, vorname FROM tl_lizenzverwaltung WHERE id = ?")
		                                 ->limit(1)
		                                 ->execute($dc->id);

		if (!$record->numRows)
		{
			return;
		}

		$myAlias = ContaoHelper::generateAlias($record->name.'-'.$record->vorname);

		Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung SET alias = ? WHERE id = ?")
		                       ->execute($myAlias, $dc->id);
	}
}
