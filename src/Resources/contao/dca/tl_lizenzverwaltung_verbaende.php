<?php

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;

/**
 * Tabelle tl_lizenzverwaltung_verbaende
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_verbaende'] = array
(

	// Config
	'config' => array
	(
		// Der Kurzname 'Table' gibt es unter Contao 5 nicht mehr, der FQCN in beiden
		'dataContainer'               => DC_Table::class,
		'switchToEdit'                => true, 
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                 => 'primary',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'filter,sort;search,limit',
			'disableGrouping'         => true,
		),
		'label' => array
		(
			'fields'                  => array('name', 'kennzeichen', 'organisation'),
			'format'                  => '%s %s',
			'showColumns'             => true,
			'label_callback'          => array('tl_lizenzverwaltung_verbaende','getRecord')
		),
		'global_operations' => array
		(
			'lizenzverwaltung' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['lizenzverwaltung'],
				'href'                => 'table=tl_lizenzverwaltung',
				'icon'                => 'bundles/contaolizenzverwaltung/images/lizenz.png',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'cut' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
			),
			'toggle' => array
			(
				'label'                => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['toggle'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,kennzeichen,organisation;{publish_legend},published'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'explanation'             => 'lizenzverwaltung_verbaende',
			'eval'                    => array
			(
				'helpwizard'          => true,
				'mandatory'           => true,
				'maxlength'           => 255, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'kennzeichen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['kennzeichen'],
			'exclude'                 => true,
			'search'                  => true,
			'explanation'             => 'lizenzverwaltung_verbaende',
			'inputType'               => 'text',
			'eval'                    => array
			(
				'helpwizard'          => true,
				'mandatory'           => true,
				'maxlength'           => 1, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(1) NOT NULL default ''"
		),
		'organisation' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['organisation'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'long clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'organisation_name' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['organisation_name'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'mandatory'         => false,
							'doNotCopy'         => true,
						),
					),
					'organisation_id' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['organisation_id'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
						)
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_verbaende']['published'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => 1,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
	)
);


/**
 * Class tl_lizenzverwaltung_verbaende
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    News
 */
class tl_lizenzverwaltung_verbaende extends Backend
{
	/**
	 * Erzeugt das Objekt.
	 *
	 * Der öffentliche Konstruktor ist Pflicht: Unter Contao 4.13 ist
	 * `Backend::__construct()` nur protected. Der frühere Aufruf
	 * `$this->import('BackendUser', 'User')` ist entfallen — er bricht unter
	 * Contao 5 ab, weil es dort keine globalen Klassenaliasse mehr gibt, und
	 * das importierte Objekt wurde ohnehin nirgends benutzt.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Ergänzt die Übersichtszeile um die untergeordneten Organisationen.
	 *
	 * @param array<string,mixed> $row   Der Verbandsdatensatz
	 * @param string              $label Der bereits erzeugte Beschriftungstext
	 * @param DataContainer       $dc    Der Data Container
	 * @param array<int,string>   $args  Die sichtbaren Spaltenwerte; Index 2 nimmt
	 *                                   die Untergliederung auf
	 *
	 * @return array<int,string> Die ergänzten Spaltenwerte; ohne hinterlegte
	 *                           Untergliederung steht dort ein Strich
	 */
	public function getRecord($row, $label, DataContainer $dc, $args)
	{
		$forwarder = StringUtil::deserialize($row['organisation'] ?? null, true);

		$daten = array();

		foreach ($forwarder as $item)
		{
			$daten[] = '<span>'.StringUtil::specialchars((string) ($item['organisation_name'] ?? '')).' ['.StringUtil::specialchars((string) ($item['organisation_id'] ?? '')).']</span>';
		}

		$args[2] = $daten ? implode('<br>', $daten) : '-';

		return $args;
	}
}
