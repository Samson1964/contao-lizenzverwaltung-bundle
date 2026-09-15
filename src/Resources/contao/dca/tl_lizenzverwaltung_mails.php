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
use Contao\Date;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper;

/**
 * Tabelle tl_lizenzverwaltung_mails
 */
$GLOBALS['TL_DCA']['tl_lizenzverwaltung_mails'] = array
(

	// Config
	'config' => array
	(
		// Der Kurzname 'Table' gibt es unter Contao 5 nicht mehr, der FQCN in beiden
		'dataContainer'               => DC_Table::class,
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
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
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
	//
	// Das frühere Palettenfeld "send" ist entfallen: Es gibt kein Feld dieses
	// Namens, "send" ist die Operation in der Übersicht. Contao ging bislang
	// stillschweigend darüber hinweg, angezeigt wurde nie etwas.
	'palettes' => array
	(
		'default'                     => '{text_legend},subject,content;{template_legend},template,signatur,preview;{mail_legend},insertLizenz,insertLizenzCard,copyVerband,copyDSB'
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
 * Rückrufe des Data Containers tl_lizenzverwaltung_mails.
 */
class tl_lizenzverwaltung_mails extends Backend
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
	 * Beschriftet eine E-Mail in der Übersicht der Lizenz.
	 *
	 * Angezeigt werden Betreff, Versandzustand und — solange die Mail noch
	 * nicht versendet wurde — eine Vorschau des Textes aus der gewählten
	 * Vorlage.
	 *
	 * @param array<string,mixed> $arrRow Der E-Mail-Datensatz
	 *
	 * @return string Der HTML-Code der Zeile
	 */
	public function listEmails($arrRow)
	{
		$versendet = $arrRow['sent_state'] && $arrRow['sent_date'];

		$kopf = '
<div class="cte_type '.($versendet ? 'published' : 'unpublished').'"><strong>'.StringUtil::specialchars((string) $arrRow['subject']).'</strong> - '
      . ($versendet ? 'Versendet am '.Date::parse(Config::get('datimFormat'), $arrRow['sent_date']) : 'Nicht versendet').'</div>';

		// Ist der versendete Text gespeichert, erübrigt sich die Vorschau.
		// Geprüft wurde hier früher das Feld "sendText", das es nie gab —
		// die Vorschau lief deshalb auch bei bereits versendeten Mails.
		if ($arrRow['sent_text'] ?? false)
		{
			return $kopf."\n";
		}

		$lizenz  = $this->fetchLizenz((int) $arrRow['pid']);
		$content = $this->renderTemplate((int) $arrRow['template'], $lizenz, (string) $arrRow['content'], (bool) $arrRow['signatur']);

		// Die Klasse limit_height gilt unter Contao 5 als veraltet; der
		// Ersatz list.sorting.limitHeight fehlt in 4.13 vollständig, für
		// beide Fassungen bleibt sie der einzige gemeinsame Weg
		return $kopf.'
<div class="limit_height'.(!Config::get('doNotCollapse') ? ' h128' : '').'">
'.StringUtil::insertTagToSrc($content).'<hr>
</div>'."\n";
	}

	/**
	 * Liefert die Auswahlliste der veröffentlichten E-Mail-Vorlagen.
	 *
	 * @param DataContainer $dc Der Data Container; wird nicht ausgewertet
	 *
	 * @return array<int|string,string> Zuordnung Vorlagen-ID => Name samt
	 *                                  Beschreibung; leer, wenn keine Vorlage
	 *                                  veröffentlicht ist
	 */
	public function getTemplates(DataContainer $dc): array
	{
		$result = Database::getInstance()->prepare("SELECT id, name, description FROM tl_lizenzverwaltung_templates WHERE published = ? ORDER BY name")
		                                 ->execute(1);

		$options = array();

		while ($result->next())
		{
			$options[$result->id] = $result->name.($result->description ? ' ('.$result->description.')' : '');
		}

		return $options;
	}

	/**
	 * Zeigt eine Vorschau der E-Mail im Bearbeitungsformular.
	 *
	 * @param DataContainer $dc Der Data Container
	 *
	 * @return string Der HTML-Code des Anzeigefeldes
	 */
	public function getPreview(DataContainer $dc): string
	{
		$record = Database::getInstance()->prepare("SELECT pid, template, content, signatur FROM tl_lizenzverwaltung_mails WHERE id = ?")
		                                 ->limit(1)
		                                 ->execute($dc->id);

		if (!$record->template)
		{
			$content = 'Keine Vorlage ausgewählt';
		}
		else
		{
			$lizenz = $this->fetchLizenz((int) $record->pid);
			$inhalt = $this->renderTemplate((int) $record->template, $lizenz, (string) $record->content, (bool) $record->signatur);

			$content = '<div class="tl_preview">'.($inhalt ?: 'Kein Template gefunden!').'</div>';
		}

		return '
<div class="long clr widget">
	<h3><label>'.($GLOBALS['TL_LANG']['tl_lizenzverwaltung_mails']['preview'][0] ?? 'Vorschau').'</label></h3>
	'.$content.'
</div>';
	}

	/**
	 * Setzt eine E-Mail-Vorlage mit den Daten einer Lizenz zusammen.
	 *
	 * Aus der Vorlage wird nur der Inhalt des body-Elements verwendet, weil
	 * die Vorschau innerhalb der Backend-Seite steht und kein vollständiges
	 * HTML-Dokument enthalten darf.
	 *
	 * @param int    $template Datensatz-ID in tl_lizenzverwaltung_templates
	 * @param object $lizenz   Zeilenobjekt aus dem Verbund von Lizenz und Person
	 * @param string $inhalt   Der redaktionelle Text der E-Mail
	 * @param bool   $signatur Ob die Signatur aus den Einstellungen angehängt wird
	 *
	 * @return string Der zusammengesetzte Text, oder eine leere Zeichenkette
	 *                wenn es die Vorlage nicht gibt oder sie kein body-Element hat
	 */
	private function renderTemplate(int $template, object $lizenz, string $inhalt, bool $signatur): string
	{
		$tpl = Database::getInstance()->prepare("SELECT template FROM tl_lizenzverwaltung_templates WHERE id = ?")
		                              ->limit(1)
		                              ->execute($template);

		if (!$tpl->numRows || !preg_match('/<body>(.*)<\/body>/s', (string) $tpl->template, $matches))
		{
			return '';
		}

		return Helper::replaceTokens(StringUtil::restoreBasicEntities($matches[1]), array
		(
			'css'               => '',
			'lizenz_vorname'    => $lizenz->vorname,
			'lizenz_nachname'   => $lizenz->name,
			'lizenz_geschlecht' => $lizenz->geschlecht,
			'lizenz_art'        => $lizenz->lizenz,
			'lizenz_nummer'     => $lizenz->license_number_dosb,
			'lizenz_content'    => $inhalt,
			'lizenz_signatur'   => $signatur ? Config::get('lizenzverwaltung_mailsignatur') : '',
		));
	}

	/**
	 * Liest eine Lizenz samt der zugehörigen Person.
	 *
	 * @param int $id Datensatz-ID in tl_lizenzverwaltung_items
	 *
	 * @return object Das Zeilenobjekt aus dem Verbund beider Tabellen
	 */
	private function fetchLizenz(int $id): object
	{
		return Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                              ->limit(1)
		                              ->execute($id);
	}
}
