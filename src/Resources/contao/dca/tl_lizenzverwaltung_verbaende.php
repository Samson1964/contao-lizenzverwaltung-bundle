<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package News
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_lizenzverwaltung_verbaende
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_verbaende'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
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
class tl_lizenzverwaltung_verbaende extends \Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @param DataContainer
     * @param array
     * @return string
     */
	public function getRecord($row, $label, \DataContainer $dc, $args)
	{
		// Weiterleitungen ergänzen
		if($row['organisation'])
		{
			$forwarder = unserialize($row['organisation']);
			$daten = array();
			foreach($forwarder as $item)
			{
				$daten[] = '<span>'.$item['organisation_name'].' ['.$item['organisation_id'].']</span>';
			}
			$args[2] = implode('<br>', $daten);
		}
		else
		{
			$args[2] = '-';
		}

		return $args;

	}

}
