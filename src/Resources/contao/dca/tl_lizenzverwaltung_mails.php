<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Table tl_lizenzverwaltung_mails
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_mails'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_lizenzverwaltung_items',
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sent_state ASC', 'sent_date DESC'),
			'headerFields'            => array('lizenz', 'erwerb', 'gueltigkeit', 'verband', 'license_number_dosb'),
			'panelLayout'             => 'filter;sort,search,limit',
			'child_record_callback'   => array('tl_lizenzverwaltung_mails', 'listEmails')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'send' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['send'],
				'href'                => 'key=send',
				'icon'                => 'bundles/contaolizenzverwaltung/images/email_senden.png'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{text_legend},subject,content;{template_legend},template,signatur,preview;{mail_legend},insertLizenz,insertLizenzCard,copyVerband,copyDSB,send'
	),

	// Fields
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
			'flag'                    => 11,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['template'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('tl_lizenzverwaltung_mails', 'getTemplates'),
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'submitOnChange'      => true
			),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		// Signatur
		'signatur' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['signatur'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => 1,
			'eval'                    => array
			(
				'mandatory'           => false,
				'tl_class'            => 'w50',
				'isBoolean'           => true,
				'submitOnChange'      => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'preview' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['preview'],
			'input_field_callback'    => array('tl_lizenzverwaltung_mails', 'getPreview'),
			'exclude'                 => false,
		),
		'subject' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['subject'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'default'                 => 'Übersendung der Trainerlizenzen',
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'maxlength'=>128, 'tl_class'=>'long clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'content' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['content'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array
			(
				'rte'                 => 'tinyMCE',
				'helpwizard'          => true,
				'tl_class'            => 'long clr',
			),
			'explanation'             => 'insertTags',
			'sql'                     => "mediumtext NULL"
		),
		'insertLizenz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['insertLizenz'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => false
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'insertLizenzCard' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['insertLizenzCard'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => false
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'copyVerband' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['copyVerband'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50 clr',
				'isBoolean'           => false
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'copyDSB' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['copyDSB'],
			'inputType'               => 'checkbox',
			'default'                 => true,
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => false
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'sent_state' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['sent_state'],
			'eval'                    => array
			(
				'doNotCopy'           => true,
				'isBoolean'           => true,
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'sent_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['sent_date'],
			'sorting'                 => true,
			'flag'                    => 6,
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'doNotCopy'           => true,
			),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'sent_text' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['sent_text'],
			'eval'                    => array
			(
				'doNotCopy'           => true,
			),
			'sql'                     => "mediumtext NULL"
		),
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_lizenzverwaltung_mails extends Backend
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
	 * List records
	 *
	 * @param array $arrRow
	 *
	 * @return string
	 */
	public function listEmails($arrRow)
	{
		// Template aus Datenbank laden
		$tpl = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_templates WHERE id=?")
		                               ->execute($arrRow['template']);

		// Lizenz- und Personen-Datensatz einlesen
		$lizenz = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                  ->execute($arrRow['pid']);

		if($tpl->numRows)
		{
			preg_match('/<body>(.*)<\/body>/s', $tpl->template, $matches); // Body extrahieren
			$content = \StringUtil::restoreBasicEntities($matches[1]); // [nbsp] und Co. ersetzen
			$arrTokens = array
			(
				'css'               => '',
				'lizenz_vorname'    => $lizenz->vorname,
				'lizenz_nachname'   => $lizenz->name,
				'lizenz_geschlecht' => $lizenz->geschlecht,
				'lizenz_content'    => $arrRow['content'],
				'lizenz_signatur'   => $arrRow['signatur'] ? $GLOBALS['TL_CONFIG']['lizenzverwaltung_mailsignatur'] : '',
			);
			$content = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($content, $arrTokens);
		}
		else
		{
			// Kein Template gefunden
			$content = 'Kein Template gefunden!';
		}

		return '
<div class="cte_type ' . (($arrRow['sent_state'] && $arrRow['sent_date']) ? 'published' : 'unpublished') . '"><strong>' . $arrRow['subject'] . '</strong> - ' . (($arrRow['sent_state'] && $arrRow['sent_date']) ? 'Versendet am '.Date::parse(Config::get('datimFormat'), $arrRow['sent_date']) : 'Nicht versendet'). '</div>
<div class="limit_height' . (!Config::get('doNotCollapse') ? ' h128' : '') . '">' . (!$arrRow['sendText'] ? '
' . \StringUtil::insertTagToSrc($content) . '<hr>' : '' ) . '
</div>' . "\n";

	}


	public function getTemplates(\DataContainer $dc)
	{

		// Neue Templates laden
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_templates WHERE published = ?")
		                                  ->execute(1);

		$options = array();
		while($result->next())
		{
			$options[$result->id] = $result->name.($result->description ? ' ('.$result->description.')' : '');
		}
		
		return $options;

		if(version_compare(VERSION.BUILD, '2.9.0', '>=') && version_compare(VERSION.BUILD, '4.8.0', '<'))
		{
			// Den 2. Parameter gibt es nur ab Contao 2.9 bis 4.7
			$options = $this->getTemplateGroup('mail_lizenzverwaltung_', $dc->activeRecord->id);
		}
		else
		{
			// Ohne 2. Parameter bis Contao 2.8 und ab Contao 4.8
			$options = $this->getTemplateGroup('mail_lizenzverwaltung_');
		}


	}

	public function getPreview(\DataContainer $dc)
	{
		// Templatestatus
		if($dc->activeRecord->template)
		{
			// Lizenz- und Personen-Datensatz einlesen
			$lizenz = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
			                                  ->execute($dc->activeRecord->pid);

			// Template aus Datenbank laden
			$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_templates WHERE id=?")
			                                  ->execute($dc->activeRecord->template);
			if($result->numRows)
			{
				preg_match('/<body>(.*)<\/body>/s', $result->template, $matches); // Body extrahieren
				$content = \StringUtil::restoreBasicEntities($matches[1]); // [nbsp] und Co. ersetzen
				$arrTokens = array
				(
					'css'               => '',
					'lizenz_vorname'    => $lizenz->vorname,
					'lizenz_nachname'   => $lizenz->name,
					'lizenz_geschlecht' => $lizenz->geschlecht,
					'lizenz_content'    => $dc->activeRecord->content,
					'lizenz_signatur'   => $dc->activeRecord->signatur ? $GLOBALS['TL_CONFIG']['lizenzverwaltung_mailsignatur'] : '',
				);
				$content = \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($content, $arrTokens);
				$content = '<div class="tl_preview">'.$content.'</div>';
			}
			else
			{
				// Kein Template gefunden
				$content = '<div class="tl_preview">Kein Template gefunden!</div>';
			}
		}
		else
		{
			$content = 'Keine Vorlage ausgewählt';
		}

		$string = '
<div class="long clr widget">
	<h3><label>'.$GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['preview'][0].'</label></h3>
	'.$content.'
</div>';

		return $string;
	}

}
