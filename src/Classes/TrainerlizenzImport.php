<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;

/**
 * Import von Lizenzen aus einer CSV-Datei.
 *
 * Die Klasse bedient den Schlüssel "import" des Backend-Moduls.
 *
 * Hinweis: Die eingelesene Spaltenfolge stammt noch aus der Contao-3-Tabelle
 * `tl_trainerlizenzen`, in der Person und Lizenz in einer Zeile lagen. Seit der
 * Aufteilung auf `tl_lizenzverwaltung` und `tl_lizenzverwaltung_items` passt
 * sie zu keiner der beiden Tabellen mehr, und es gibt im DCA auch keine
 * Operation, die hierher verweist. Der Einstieg wird deshalb nur noch
 * lauffähig gehalten, nicht mehr gepflegt.
 */
class TrainerlizenzImport extends Backend
{
	/**
	 * Spalten, die aus der CSV-Datei übernommen werden, in der erwarteten Reihenfolge.
	 *
	 * @var array<int,string>
	 */
	private const SPALTEN = array
	(
		'name', 'vorname', 'geburtstag', 'strasse', 'plz', 'ort', 'email', 'verband',
		'lizenznummer', 'lizenz', 'erwerb', 'verlaengerung1', 'verlaengerung2',
		'verlaengerung3', 'verlaengerung4', 'gueltigkeit', 'published', 'letzteAenderung',
	);

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
	 * Zeigt das Formular zur Auswahl einer CSV-Datei und liest sie ein.
	 *
	 * @param DataContainer $dc Der Data Container; die Zieltabelle wird daraus
	 *                          übernommen
	 *
	 * @return string Der HTML-Code des Formulars. Nach erfolgreichem Import
	 *                kehrt die Methode nicht zurück, sondern leitet auf die
	 *                Übersicht zurück.
	 */
	public function importTrainer(DataContainer $dc): string
	{
		if (Input::get('key') !== 'import')
		{
			return '';
		}

		$objUploader = new ($this->getUploaderClass())();

		if (Input::post('FORM_SUBMIT') === 'tl_table_import')
		{
			$arrUploaded = $objUploader->uploadTo('system/tmp');

			if (empty($arrUploaded))
			{
				Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields'] ?? 'Es wurde keine Datei hochgeladen.');
				$this->reload();
			}

			foreach ($arrUploaded as $strCsvFile)
			{
				$objFile = new File($strCsvFile);

				if ($objFile->extension !== 'csv')
				{
					Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'] ?? 'Dateityp %s ist nicht erlaubt.', $objFile->extension));
					continue;
				}

				$this->importFile($objFile, (string) $dc->table);
			}

			System::setCookie('BE_PAGE_OFFSET', 0, 0);
			$this->redirect(str_replace('&key=table', '', Environment::get('request')));
		}

		return '
<div id="tl_buttons">
<a href="'.StringUtil::ampersand(str_replace('&key=table', '', Environment::get('request'))).'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle'] ?? '').'" accesskey="b">'.($GLOBALS['TL_LANG']['MSC']['backBT'] ?? 'Zurück').'</a>
</div>

<h2 class="sub_headline">'.($GLOBALS['TL_LANG']['MSC']['tw_import'][1] ?? 'CSV-Datei importieren').'</h2>
'.Message::generate().'
<form action="'.StringUtil::ampersand(Environment::get('request')).'" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_table_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.Helper::getRequestToken().'">

<div class="tl_tbox">
  <h3><label for="separator">'.($GLOBALS['TL_LANG']['MSC']['separator'][0] ?? 'Feldtrennzeichen').'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="semicolon">'.($GLOBALS['TL_LANG']['MSC']['semicolon'] ?? 'Semikolon').'</option>
    <option value="comma">'.($GLOBALS['TL_LANG']['MSC']['comma'] ?? 'Komma').'</option>
    <option value="tabulator">'.($GLOBALS['TL_LANG']['MSC']['tabulator'] ?? 'Tabulator').'</option>
  </select>'.(!empty($GLOBALS['TL_LANG']['MSC']['separator'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  <h3>'.($GLOBALS['TL_LANG']['MSC']['source'][0] ?? 'Quelldatei').'</h3>'.$objUploader->generateMarkup().(isset($GLOBALS['TL_LANG']['MSC']['source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][0] ?? 'Importieren').'">
</div>

</div>
</form>';
	}

	/**
	 * Liest eine einzelne CSV-Datei zeilenweise in die Zieltabelle ein.
	 *
	 * Bis Fassung 4.3.6 wurden die Werte mit `addslashes()` in eine
	 * zusammengesetzte INSERT-Anweisung geschrieben. Das war angreifbar und
	 * brach außerdem an jedem Zeichensatz, für den `addslashes()` nicht die
	 * passende Maskierung liefert. Jetzt wird je Zeile eine vorbereitete
	 * Anweisung ausgeführt.
	 *
	 * @param File   $objFile Die hochgeladene Datei
	 * @param string $table   Name der Zieltabelle
	 *
	 * @return void Zeilen mit zu wenigen Feldern werden übersprungen; ihre
	 *              Anzahl wird als Hinweis im Backend gemeldet
	 */
	private function importFile(File $objFile, string $table): void
	{
		$strSeparator = match (Input::post('separator'))
		{
			'semicolon' => ';',
			'tabulator' => "\t",
			default     => ',',
		};

		$sql = "INSERT INTO $table (".implode(', ', self::SPALTEN).') VALUES ('.implode(', ', array_fill(0, \count(self::SPALTEN), '?')).')';

		$resFile     = $objFile->handle;
		$uebersprungen = 0;
		$eingelesen    = 0;

		while (false !== ($arrRow = fgetcsv($resFile, null, $strSeparator, '"', '\\')))
		{
			if (\count($arrRow) < \count(self::SPALTEN))
			{
				++$uebersprungen;
				continue;
			}

			Database::getInstance()->prepare($sql)
			                       ->execute(...\array_slice($arrRow, 0, \count(self::SPALTEN)));

			++$eingelesen;
		}

		Message::addConfirmation($eingelesen.' Zeile(n) eingelesen.');

		if ($uebersprungen > 0)
		{
			Message::addInfo($uebersprungen.' Zeile(n) übersprungen, weil sie weniger als '.\count(self::SPALTEN).' Felder enthielten.');
		}
	}

	/**
	 * Ermittelt die Klasse, mit der Dateien hochgeladen werden.
	 *
	 * Der Benutzer legt in seinem Profil einen Uploader fest, dort steht aber
	 * nur der kurze Klassenname ohne Namensraum. Unter Contao 5 gibt es keine
	 * globalen Klassenaliasse mehr, deshalb wird der Namensraum ergänzt. Ist
	 * der Wert unbrauchbar, greift der einfache Uploader, den es in beiden
	 * Fassungen gibt.
	 *
	 * @return class-string Der voll qualifizierte Klassenname
	 */
	private function getUploaderClass(): string
	{
		$class = (string) BackendUser::getInstance()->uploader;

		if ($class && class_exists('Contao\\'.$class))
		{
			return 'Contao\\'.$class;
		}

		return FileUpload::class;
	}
}
