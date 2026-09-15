<?php

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\DC_Table;

/**
 * Tabelle tl_lizenzverwaltung_templates
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_templates'] = array
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
			'fields'                  => array('name', 'description'),
			'format'                  => '%s %s',
			'showColumns'             => true,
		),
		'global_operations' => array
		(
			'lizenzverwaltung' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['lizenzverwaltung'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'cut' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
			),
			'toggle' => array
			(
				'label'                => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['toggle'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,description,template;{publish_legend},published'
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true,
				'maxlength'           => 255, 
				'tl_class'            => 'long'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['description'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => false,
				'maxlength'           => 255, 
				'tl_class'            => 'long'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['template'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'default'                 => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['default_template'],
			'eval'                    => array
			(
				'mandatory'           => true, 
				'preserveTags'        => true, 
				'decodeEntities'      => true, 
				'class'               => 'monospace', 
				'rte'                 => 'ace|php', 
				'helpwizard'          => true, 
				'tl_class'            => 'long'
			),
			'explanation'             => 'lizenzverwaltung_templates',
			'sql'                     => "text NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_templates']['published'],
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
 * Class tl_lizenzverwaltung_templates
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    News
 */
class tl_lizenzverwaltung_templates extends Backend
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
}
