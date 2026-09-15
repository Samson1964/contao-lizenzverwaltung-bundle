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
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\FilesModel;
use Contao\Image;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoHelperBundle\Classes\Helper as ContaoHelper;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper;

/**
 * Tabelle tl_lizenzverwaltung_items
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_items'] = array
(
	// Config
	'config' => array
	(
		// Der Kurzname 'Table' gibt es unter Contao 5 nicht mehr, der FQCN in beiden
		'dataContainer'               => DC_Table::class,
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
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'                => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['toggle'],
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
		'default'                     => 'verification,{leitfaden_legend:hide},leitfaden;{dosb_legend},license_number_dosb,button_license,view_pdf,button_pdf,view_pdfcard,button_pdfcard;{marker_legend},marker;{verband_legend},verband;{lizenz_legend},lizenznummer,lizenz;{lizenzver_legend},erwerb,verlaengerungen;{lizenzbis_legend},gueltigkeit;{codex_legend},codex,help;{datum_legend},letzteAenderung,setHeute;{hinweise_legend:hide},addEnclosure,bemerkung;{published_legend},published'
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
		// Gibt einen Link zum LiMS-Leitfaden aus
		'leitfaden' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['leitfaden'],
			'input_field_callback'    => array('tl_lizenzverwaltung_items', 'getLeitfaden'),
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
			// Als Rückruf statt als feste Liste: Sonst läuft die Datenbankabfrage
			// bei jedem Laden der DCA, auch wenn das Feld gar nicht gezeigt wird
			'options_callback'        => static fn () => Helper::getVerbaende(),
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
			'options_callback'        => static fn () => Helper::getLizenzen(),
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
				'tl_class'            => 'long clr',
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
							'style'               => 'width:90%'
						),
					),
					'seminar' => array
					(
						'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['verlaengerung_seminar'],
						'exclude'                 => true,
						'inputType'               => 'text',
						'eval'                    => array
						(
							'tl_class'            => 'wizard',
							'rgxp'                => 'date',
							'datepicker'          => true,
							'maxlength'           => 10,
							'style'               => 'width:90%'
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
			'explanation'             => 'lizenzverwaltung_verlaengerung',
			'exclude'                 => true,
			'flag'                    => 8,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'helpwizard'          => true,
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
			'exclude'                 => true,
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'long',
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
			'explanation'             => 'lizenzverwaltung_codex',
			'flag'                    => 8,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'helpwizard'          => true,
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
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'long',
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
			'explanation'             => 'lizenzverwaltung_codex',
			'flag'                    => 8,
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


/**
 * Rückrufe des Data Containers tl_lizenzverwaltung_items.
 */
class tl_lizenzverwaltung_items extends Backend
{
	/**
	 * Erzeugt das Objekt.
	 *
	 * Der öffentliche Konstruktor ist Pflicht: Unter Contao 4.13 ist
	 * `Backend::__construct()` nur protected.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Zeichnet den Knopf zum E-Mail-Postfach einer Lizenz.
	 *
	 * Das Symbol zeigt an, wie weit die Adressen für einen Versand reichen:
	 * grün, wenn sowohl der Trainer als auch der Verbandsreferent eine Adresse
	 * hat, gelb bei nur einer von beiden, grau wenn keine hinterlegt ist.
	 *
	 * @param array<string,mixed> $row        Der Datensatz
	 * @param string              $href       Ziel der Operation
	 * @param string              $label      Beschriftung des Knopfes
	 * @param string              $title      Titel des Knopfes
	 * @param string              $icon       Vorgabesymbol aus der DCA
	 * @param string              $attributes Zusätzliche HTML-Attribute
	 *
	 * @return string Der HTML-Code des Knopfes
	 */
	public function toggleEmail($row, $href, $label, $title, $icon, $attributes)
	{
		$href .= '&amp;id='.$row['id'];

		$verband_email = Helper::getVerbandMail((string) $row['verband']);
		$person_email  = Helper::getPersonMail($row['pid']);

		if ($person_email && $verband_email)
		{
			$icon = 'bundles/contaolizenzverwaltung/images/email.png';
		}
		elseif ($person_email || $verband_email)
		{
			$icon = 'bundles/contaolizenzverwaltung/images/email_gelb.png';
		}
		else
		{
			$icon = 'bundles/contaolizenzverwaltung/images/email_grau.png';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}

	/**
	 * Wandelt die Datumsspalten der Übersicht in ein lesbares Format.
	 *
	 * @param array<string,mixed> $row   Der Datensatz
	 * @param string              $label Der bereits erzeugte Beschriftungstext
	 * @param DataContainer       $dc    Der Data Container
	 * @param array<int,string>   $args  Die sichtbaren Spaltenwerte
	 *
	 * @return array<int,string> Die umgewandelten Spaltenwerte
	 */
	public function convertDate($row, $label, DataContainer $dc, $args)
	{
		foreach ($args as $x => $wert)
		{
			$args[$x] = ContaoHelper::getDate($wert);
		}

		return $args;
	}

	/**
	 * Zeigt die DOSB-Lizenznummer samt Verweis ins LiMS an.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Anzeigefeldes; ohne Lizenznummer ein Hinweistext
	 */
	public function getLizenznummer(DataContainer $dc): string
	{
		$record = $this->fetch($dc, array('license_number_dosb', 'lid'));
		$link   = rtrim((string) Config::get('lims_link'), '/');

		if ($record->license_number_dosb && $link)
		{
			$status = '<b>'.StringUtil::specialchars((string) $record->license_number_dosb).'&nbsp;&nbsp;</b>'
			        . '<a href="'.StringUtil::specialchars($link.'/dosb_license/'.$record->lid).'" target="_blank" rel="noopener" class="dosb_button_mini">Ansehen</a>';
		}
		elseif ($record->license_number_dosb)
		{
			$status = '<b>'.StringUtil::specialchars((string) $record->license_number_dosb).'</b>';
		}
		else
		{
			$status = 'Keine DOSB-Lizenz vorhanden';
		}

		return '
		<div class="w50 dosb_margin">
		<div class="tl_text" style="border:0">'.$status.'</div>
		</div>';
	}

	/**
	 * Zeigt den Knopf zum Erstellen oder Verlängern der Lizenz.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Knopfes samt Angabe zum letzten Abruf
	 */
	public function getLizenzbutton(DataContainer $dc): string
	{
		$record = $this->fetch($dc, array('dosb_tstamp', 'dosb_code', 'dosb_antwort'));

		return $this->renderAbrufButton(
			'getLizenz',
			$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_license'][0] ?? 'Lizenz erstellen',
			(int) $record->dosb_tstamp,
			(int) $record->dosb_code,
			(string) $record->dosb_antwort
		);
	}

	/**
	 * Zeigt den Knopf zum PDF-Abruf im DIN-A4-Format.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Knopfes; ohne DOSB-Lizenznummer nur ein
	 *                leeres Feld, damit das Spaltenraster erhalten bleibt
	 */
	public function getLizenzPDF(DataContainer $dc): string
	{
		$record = $this->fetch($dc, array('license_number_dosb', 'dosb_pdf_tstamp', 'dosb_pdf_code', 'dosb_pdf_antwort'));

		if (!$record->license_number_dosb)
		{
			return '<div class="w50 dosb_margin"></div>';
		}

		return $this->renderAbrufButton(
			'getLizenzPDF',
			$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdf'][0] ?? 'PDF abrufen',
			(int) $record->dosb_pdf_tstamp,
			(int) $record->dosb_pdf_code,
			(string) $record->dosb_pdf_antwort
		);
	}

	/**
	 * Zeigt den Knopf zum PDF-Abruf im Kartenformat.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Knopfes; ohne DOSB-Lizenznummer nur ein leeres Feld
	 */
	public function getLizenzPDFCard(DataContainer $dc): string
	{
		$record = $this->fetch($dc, array('license_number_dosb', 'dosb_pdfcard_tstamp', 'dosb_pdfcard_code', 'dosb_pdfcard_antwort'));

		if (!$record->license_number_dosb)
		{
			return '<div class="w50 dosb_margin"></div>';
		}

		return $this->renderAbrufButton(
			'getLizenzPDFCard',
			$GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['button_pdfcard'][0] ?? 'PDF-Karte abrufen',
			(int) $record->dosb_pdfcard_tstamp,
			(int) $record->dosb_pdfcard_code,
			(string) $record->dosb_pdfcard_antwort
		);
	}

	/**
	 * Baut einen Abrufknopf samt Angabe zum letzten Abruf.
	 *
	 * @param string $key      Schlüssel des Backend-Moduls, etwa "getLizenzPDF"
	 * @param string $label    Beschriftung des Knopfes
	 * @param int    $tstamp   Zeitpunkt des letzten Abrufs; 0 wenn noch keiner erfolgte
	 * @param int    $code     HTTP-Code des letzten Abrufs
	 * @param string $antwort  Antworttext des letzten Abrufs
	 *
	 * @return string Der HTML-Code; die Angabe zum letzten Abruf wird rot
	 *                dargestellt, wenn der Code nicht 200 war
	 */
	private function renderAbrufButton(string $key, string $label, int $tstamp, int $code, string $antwort): string
	{
		// key hinzufügen, act=edit löschen
		$link = str_replace('&amp;act=edit', '', Controller::addToUrl('key='.$key.'&amp;rt='.Helper::getRequestToken()));

		$hinweis = '';

		if ($tstamp)
		{
			$css     = 200 === $code ? '' : 'color:red;';
			$hinweis = '
			<p class="tl_help tl_tip" title="" style="margin-left:7px;'.$css.'">Letzter Abruf: '
			         . date('d.m.Y H:i:s', $tstamp).' ('.$code.' '.StringUtil::specialchars($antwort).')</p>';
		}

		return '
			<div class="w50 dosb_margin">
			<div class="tl_text" style="border:0;"><a href="'.$link.'" class="dosb_button">'.$label.'</a></div>'.$hinweis.'
			</div>';
	}

	/**
	 * Platzhalterfeld für das Setzen des heutigen Änderungsdatums.
	 *
	 * Das Feld ist per Stil ausgeblendet und ohne Funktion; es steht seit
	 * jeher als Entwurf in der Palette. Belassen, damit die Palette nicht
	 * bricht.
	 *
	 * @param DataContainer $dc Der Data Container; wird nicht ausgewertet
	 *
	 * @return string Der HTML-Code des ausgeblendeten Feldes
	 */
	public function setHeute(DataContainer $dc): string
	{
		return '
<div class="w50 widget" style="display:none">
	<span class="dosb_button_mini">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['setHeute'][0] ?? '').'</span>
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['setHeute'][1] ?? '').'</p>
</div>';
	}

	/**
	 * Beschriftet eine Lizenz in der Übersicht der Person.
	 *
	 * @param array<string,mixed> $row Der Lizenzdatensatz
	 *
	 * @return string Der HTML-Code der Zeile; abgelaufene Lizenzen rot, gültige grün
	 */
	public function listLizenzen($row)
	{
		$temp = $row['gueltigkeit'] < time() ? '<span style="color: red;">' : '<span style="color: green;">';

		$temp .= $row['marker'] ? '<img src="bundles/contaolizenzverwaltung/images/marker.png" alt="" title="Lizenz ist markiert"> ' : '';
		$temp .= '<b>'.StringUtil::specialchars((string) $row['lizenz']).'</b> ';
		$temp .= date('d.m.Y', (int) $row['gueltigkeit']).' ';
		$temp .= '- '.Helper::getVerband((string) $row['verband']).' ';

		if ($row['license_number_dosb'])
		{
			$temp .= '(DOSB-Lizenz <i>'.StringUtil::specialchars((string) $row['license_number_dosb']).'</i> ';
			$temp .= 'abgerufen am '.date('d.m.Y H:i', (int) $row['dosb_tstamp']).' - Code: '.$row['dosb_code'].' '.StringUtil::specialchars((string) $row['dosb_antwort']).')</span>';
		}
		else
		{
			$temp .= '(noch nicht beim DOSB gemeldet)</span>';
		}

		return $temp;
	}

	/**
	 * Zeigt den Verweis auf den Leitfaden zum Lizenzmanagementsystem.
	 *
	 * @param DataContainer $dc Der Data Container; wird nicht ausgewertet
	 *
	 * @return string Der HTML-Code des Anzeigefeldes
	 */
	public function getLeitfaden(DataContainer $dc): string
	{
		return '
		<div class="long widget">
		<div class="tl_text" style="border:0;"><a href="bundles/contaolizenzverwaltung/pdf/Leitfaden_LiMS_09.04.2019.pdf" target="_blank" rel="noopener" style="color:blue;">Leitfaden zum Lizenzmanagementsystem</a><span> (Version 7.0 vom 09.04.2019)</span></div>
		</div>';
	}

	/**
	 * Warnt vor Werten, die der DOSB nicht annehmen wird.
	 *
	 * Geprüft wird das Gültigkeitsdatum gegen die Fristen des DOSB (zwei Jahre
	 * bei A-Lizenzen, sonst vier, jeweils ab der letzten Verlängerung und bis
	 * zum Jahresende gerundet) sowie das Vorhandensein einer E-Mail-Adresse.
	 * Bei einer bereits vergebenen DOSB-Lizenznummer ist ein zu spätes Datum
	 * ein Fehler, sonst nur ein Hinweis: Bestandsdaten dürfen abweichen.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Immer eine leere Zeichenkette; die Meldungen erscheinen
	 *                über Contaos Meldungsbereich
	 */
	public function getVerification(DataContainer $dc): string
	{
		$record = $this->fetch($dc, array('erwerb', 'verlaengerungen', 'lizenz', 'license_number_dosb', 'gueltigkeit', 'tstamp', 'pid'));

		$verlaengerung = Helper::getVerlaengerung($record->erwerb, $record->verlaengerungen);

		$gueltigkeit = match (substr((string) $record->lizenz, 0, 1))
		{
			'A'      => $this->getQuartalsende(strtotime('+2 years', $verlaengerung) - 86400),
			'B', 'C' => $this->getQuartalsende(strtotime('+4 years', $verlaengerung) - 86400),
			default  => 0,
		};

		if ($record->gueltigkeit > $gueltigkeit)
		{
			$text = 'Gültig bis ('.date('d.m.Y', (int) $record->gueltigkeit).') ist größer als erlaubt. Der DOSB erlaubt nur den '.date('d.m.Y', $gueltigkeit).'!';

			if ($record->license_number_dosb)
			{
				Message::addError($text);
			}
			else
			{
				Message::addInfo($text.' Es wird Probleme bei Updates geben.');
			}
		}

		$person = Database::getInstance()->prepare("SELECT email FROM tl_lizenzverwaltung WHERE id = ?")
		                                 ->limit(1)
		                                 ->execute($record->pid);

		if (!$person->email && $record->tstamp)
		{
			Message::addError('E-Mail-Adresse des Trainers fehlt! Ein automatischer Lizenzversand an ihn ist nicht möglich.');
		}

		return '';
	}

	/**
	 * Rundet einen Zeitstempel auf das Ende des Kalenderjahres auf.
	 *
	 * Bis 2022 endeten Lizenzen zum Quartalsende; seit 2023 gilt einheitlich
	 * das Jahresende. Die Methode heißt aus Rücksicht auf bestehende Aufrufe
	 * weiterhin so.
	 *
	 * @param int $value Beliebiger Zeitstempel
	 *
	 * @return int Zeitstempel des 31. Dezember desselben Jahres, 0 Uhr
	 */
	public function getQuartalsende(int $value): int
	{
		return (int) mktime(0, 0, 0, 12, 31, (int) date('Y', $value));
	}

	/**
	 * Wandelt ein Datum der Form JJJJMMTT in einen Zeitstempel.
	 *
	 * @param string $value Das Datum als achtstellige Zeichenkette
	 *
	 * @return int Der Zeitstempel für 0 Uhr des Tages
	 */
	public function getDate($value): int
	{
		return (int) mktime(0, 0, 0, (int) substr($value, 4, 2), (int) substr($value, 6, 2), (int) substr($value, 0, 4));
	}

	/**
	 * Zeigt die dem Datensatz angehängten Dateien an.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Anzeigefeldes
	 */
	public function viewEnclosureInfo(DataContainer $dc): string
	{
		$record  = $this->fetch($dc, array('enclosure'));
		$dateien = StringUtil::deserialize($record->enclosure, true);

		$antwort = '';

		if ($dateien)
		{
			$antwort = '<ul>';

			foreach ($dateien as $item)
			{
				$objFile = FilesModel::findByUuid($item);

				if (null === $objFile)
				{
					continue;
				}

				$arrMeta = StringUtil::deserialize($objFile->meta, true);

				$antwort .= '<li style="clear:both;">';

				if (\in_array($objFile->extension, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true))
				{
					$vorschau = $this->getThumbnail($objFile->path);

					if ('' !== $vorschau)
					{
						$antwort .= '<a href="'.StringUtil::specialchars($objFile->path).'"><img src="'.StringUtil::specialchars($vorschau).'" alt="" style="float:left; margin-right:5px; margin-bottom:5px;"></a> ';
					}
				}

				$antwort .= StringUtil::specialchars($objFile->path).'<br>';
				$antwort .= '<i>'.StringUtil::specialchars((string) ($arrMeta['de']['title'] ?? '')).'</i>';
				$antwort .= '</li>';
			}

			$antwort .= '<li style="clear:both;"></li></ul>';
		}

		return '
<div class="clr widget">
	<h3><label for="ctrl_enclosureInfo">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['enclosureInfo'][0] ?? 'Angehängte Dateien').'</label></h3>
	'.$antwort.'
	<p class="tl_help tl_tip" title="" style="margin-top:3px;">'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung_items']['enclosureInfo'][1] ?? '').'</p>
</div>';
	}

	/**
	 * Zeigt den Verweis auf die gespeicherte Lizenzurkunde im DIN-A4-Format.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Anzeigefeldes
	 */
	public function viewPDF(DataContainer $dc): string
	{
		return $this->renderPdfLink($dc, '', 'PDF DIN A4 anzeigen', 'Zeigt die auf dem DSB-Server gespeicherte Lizenzurkunde an.', 'Kein PDF DIN A4 vorhanden');
	}

	/**
	 * Zeigt den Verweis auf die gespeicherte Lizenzurkunde im Kartenformat.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Anzeigefeldes
	 */
	public function viewPDFCard(DataContainer $dc): string
	{
		return $this->renderPdfLink($dc, '-card', 'PDF Karte anzeigen', 'Zeigt die auf dem DSB-Server gespeicherte Lizenzurkunde im Format Card an.', 'Kein PDF Card vorhanden');
	}

	/**
	 * Baut den Verweis auf eine im Lizenzordner abgelegte Urkunde.
	 *
	 * @param DataContainer $dc      Der Data Container
	 * @param string        $suffix  Dateizusatz vor der Endung, leer für DIN A4
	 * @param string        $label   Beschriftung des Verweises
	 * @param string        $title   Titel des Verweises
	 * @param string        $fehlt   Text, wenn die Datei nicht vorhanden ist
	 *
	 * @return string Der HTML-Code; ohne DOSB-Lizenznummer ein leeres Feld,
	 *                damit das Spaltenraster erhalten bleibt
	 */
	private function renderPdfLink(DataContainer $dc, string $suffix, string $label, string $title, string $fehlt): string
	{
		$record = $this->fetch($dc, array('license_number_dosb'));

		if (!$record->license_number_dosb)
		{
			return '<div class="w50 dosb_margin"></div>';
		}

		$uuid   = $GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner'] ?? '';
		$ordner = $uuid ? FilesModel::findByUuid($uuid) : null;

		$status = $fehlt;
		$info   = '';

		if (null !== $ordner)
		{
			$relativ = $ordner->path.'/'.$record->license_number_dosb.$suffix.'.pdf';
			$absolut = Helper::getProjectDir().'/'.$relativ;

			if (file_exists($absolut))
			{
				$status = '<a href="'.StringUtil::specialchars($relativ).'" target="_blank" rel="noopener" title="'.StringUtil::specialchars($title).'" class="dosb_button_mini">'.$label.'</a>';
				$info   = 'Datum: '.date('d.m.Y H:i:s', (int) filemtime($absolut));
			}
		}

		return '
			<div class="w50 dosb_margin">
			<div class="tl_text" style="border:0;">'.$status.'</div>
			<p class="tl_help tl_tip" title="" style="margin-left:7px;">'.$info.'</p>
			</div>';
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
	 * Liest die genannten Felder des gerade bearbeiteten Datensatzes.
	 *
	 * Gelesen wird über die Datenbank statt über `$dc->activeRecord`: Diese
	 * Eigenschaft gilt ab Contao 5 als veraltet und ist im
	 * `input_field_callback` nicht in jedem Fall gefüllt. `$dc->id` liefert in
	 * beiden Fassungen die Datensatz-ID.
	 *
	 * @param DataContainer     $dc     Der Data Container
	 * @param array<int,string> $felder Die zu lesenden Spaltennamen
	 *
	 * @return object Das Zeilenobjekt; ohne Datensatz-ID ein Objekt, dessen
	 *                Felder alle null sind
	 */
	private function fetch(DataContainer $dc, array $felder): object
	{
		return Database::getInstance()->prepare("SELECT ".implode(', ', $felder)." FROM tl_lizenzverwaltung_items WHERE id = ?")
		                              ->limit(1)
		                              ->execute($dc->id);
	}
}
