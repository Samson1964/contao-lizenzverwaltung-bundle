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
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Email;
use Contao\FilesModel;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;

/**
 * Vorschau und Versand der Lizenz-E-Mails.
 *
 * Die Klasse bedient den Schlüssel "send" des Backend-Moduls. Beim ersten
 * Aufruf zeigt sie eine Vorschau samt Empfängerfeldern, beim zweiten — mit
 * gültigem Einmal-Token — verschickt sie die Mail und vermerkt den Versand am
 * Datensatz.
 */
class Mailer extends Backend
{
	/**
	 * Schlüssel des Einmal-Tokens in der Sitzung.
	 */
	private const SESSION_TOKEN = 'tl_lizenzverwaltung_send';

	/**
	 * Formatierung, die der versendeten Mail als Stilblock vorangestellt wird.
	 */
	private const MAIL_CSS = '<style>
	* { font-family:Calibri,Verdana,sans-serif,Arial; font-size:16px; }
</style>';

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
	 * Zeigt die Versandmaske an und verschickt die E-Mail.
	 *
	 * Der Versand ist durch ein Einmal-Token in der Sitzung abgesichert: Die
	 * Maske legt es an, das abgeschickte Formular muss es mitbringen, und
	 * unmittelbar nach dem Versand wird es gelöscht. Ein versehentliches
	 * Neuladen verschickt die Mail deshalb kein zweites Mal.
	 *
	 * @param DataContainer $dc Der Data Container; ausgewertet wird daraus die
	 *                          ID des E-Mail-Datensatzes in tl_lizenzverwaltung_mails
	 *
	 * @return string Der HTML-Code der Versandmaske. Nach erfolgreichem Versand
	 *                kehrt die Methode nicht zurück, sondern leitet zur
	 *                Mailübersicht der Lizenz zurück.
	 *
	 * @throws \Exception Wenn eine der eingetragenen Adressen ungültig ist
	 */
	public function send(DataContainer $dc): string
	{
		$mail    = $this->fetchRow('tl_lizenzverwaltung_mails', (int) $dc->id);
		$tpl     = $this->fetchRow('tl_lizenzverwaltung_templates', (int) $mail->template);
		$trainer = $this->fetchLizenz((int) $mail->pid);

		$referenten = Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE verband = ? AND published = ?")
		                                     ->execute($trainer->verband, 1);

		$dsbreferenten = Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE verband = ? AND published = ?")
		                                        ->execute('S', 1);

		$preview_css  = $this->getPreview((int) $dc->id, (int) $mail->pid, (int) $mail->template, true, self::MAIL_CSS);
		$preview_body = $this->getPreview((int) $dc->id, (int) $mail->pid, (int) $mail->template, false);

		$lizenzfilenameA4   = $this->findLizenzPdf($trainer, '', (bool) $mail->insertLizenz);
		$lizenzfilenameCard = $this->findLizenzPdf($trainer, '-card', (bool) $mail->insertLizenzCard);

		$absender = (string) Config::get('lizenzverwaltung_absender');
		$session  = Helper::getSession();

		// Versandlauf: nur mit gültigem Einmal-Token aus der Maske
		if (Input::get('token') && null !== $session && Input::get('token') === $session->get(self::SESSION_TOKEN))
		{
			$session->remove(self::SESSION_TOKEN);

			$to  = $this->parseAddresses(Input::get('an'));
			$cc  = $this->parseAddresses(Input::get('cc'));
			$bcc = $this->parseAddresses(Input::get('bcc'));

			foreach (array_merge($to, $cc, $bcc) as $email)
			{
				if (!self::validateEmail($email))
				{
					throw new \Exception(sprintf($GLOBALS['TL_LANG']['Lizenzverwaltung']['emailCorrupt'] ?? 'Ungültige E-Mail-Adresse: %s', $email));
				}
			}

			$objEmail = new Email();

			if ($lizenzfilenameA4)
			{
				$objEmail->attachFile($lizenzfilenameA4);
			}

			if ($lizenzfilenameCard)
			{
				$objEmail->attachFile($lizenzfilenameCard);
			}

			// Absender liegt als "Name <adresse>" in den Einstellungen
			preg_match('~(?:([^<]*?)\s*)?<(.*)>~', $absender, $arrFrom);

			$objEmail->from     = $arrFrom[2] ?? $absender;
			$objEmail->fromName = $arrFrom[1] ?? '';
			$objEmail->subject  = $mail->subject;
			$objEmail->logFile  = 'lizenzverwaltung_email.log';
			$objEmail->html     = $preview_css;

			if ($cc)
			{
				$objEmail->sendCc($cc);
			}

			if ($bcc)
			{
				$objEmail->sendBcc($bcc);
			}

			if ($objEmail->sendTo($to))
			{
				Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_mails %s WHERE id = ?")
				                       ->set(array
				                       (
				                           'sent_date'  => time(),
				                           'sent_state' => 1,
				                           'sent_text'  => $preview_body,
				                       ))
				                       ->execute($dc->id);

				Message::addConfirmation('E-Mail versendet');

				Controller::redirect(Helper::getBackendRoute().'?do='.Input::get('do').'&table='.Input::get('table').'&id='.$mail->pid);
			}

			Message::addError('Die E-Mail konnte nicht versendet werden.');
		}

		// Empfängerfelder vorbelegen
		$email_an  = $trainer->email ? StringUtil::specialchars($trainer->vorname.' '.$trainer->name.' <'.$trainer->email.'>') : '';
		$email_cc  = '';
		$email_bcc = '';

		if ($mail->copyVerband)
		{
			$adressen = array();

			while ($referenten->next())
			{
				$adressen[] = $referenten->vorname.' '.$referenten->nachname.' <'.$referenten->email.'>';
			}

			$email_cc = StringUtil::specialchars(implode(', ', $adressen));
		}

		if ($mail->copyDSB)
		{
			$adressen = array($absender);

			while ($dsbreferenten->next())
			{
				$adressen[] = $dsbreferenten->vorname.' '.$dsbreferenten->nachname.' <'.$dsbreferenten->email.'>';
			}

			$email_bcc = StringUtil::specialchars(implode(', ', $adressen));
		}

		$strToken = bin2hex(random_bytes(16));

		if (null !== $session)
		{
			$session->set(self::SESSION_TOKEN, $strToken);
		}

		return
		'<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle'] ?? '').'" accesskey="b">'.($GLOBALS['TL_LANG']['MSC']['backBT'] ?? 'Zurück').'</a>
</div>
'.Message::generate().'
<form action="'.Helper::getBackendRoute().'" id="tl_lizenzverwaltung_send" class="tl_form" method="get">
<div class="tl_formbody_edit tl_lizenzverwaltung_send">
<input type="hidden" name="do" value="'.StringUtil::specialchars((string) Input::get('do')).'">
<input type="hidden" name="table" value="'.StringUtil::specialchars((string) Input::get('table')).'">
<input type="hidden" name="key" value="'.StringUtil::specialchars((string) Input::get('key')).'">
<input type="hidden" name="id" value="'.StringUtil::specialchars((string) Input::get('id')).'">
<input type="hidden" name="token" value="'.$strToken.'">
<div class="tl_preview">
<table class="prev_header">
  <tr class="row_0">
    <td class="col_0"><b>Absender:</b></td>
    <td class="col_1">'.StringUtil::specialchars($absender).'</td>
  </tr>
  <tr class="row_1">
    <td class="col_0"><b>Betreff:</b></td>
    <td class="col_1">'.StringUtil::specialchars((string) $mail->subject).'</td>
  </tr>
  <tr class="row_2">
    <td class="col_0"><b>E-Mail-Template:</b></td>
    <td class="col_1">'.StringUtil::specialchars((string) $tpl->name).'</td>
  </tr>
</table>
</div>
<div class="tl_preview">'.$preview_body.'</div>

<div class="tl_tbox">
<div class="long widget">
  <b>Lizenz-PDF DIN A4:</b> <span>&nbsp;&nbsp;'.($lizenzfilenameA4 ? 'Wird mitgeschickt.' : 'Nicht vorhanden oder wird nicht mitgeschickt.').'</span>
</div>
<div class="long widget">
  <b>Lizenz-PDF Karte:</b> <span>&nbsp;&nbsp;'.($lizenzfilenameCard ? 'Wird mitgeschickt.' : 'Nicht vorhanden oder wird nicht mitgeschickt.').'</span>
</div>
<div class="long widget">
  <h3><label for="ctrl_an">An<span class="mandatory">*</span></label></h3>
  <input type="text" name="an" id="ctrl_an" value="'.$email_an.'" class="tl_text" onfocus="Backend.getScrollOffset()">
  <p class="tl_help tl_tip">Pflichtfeld: Empfänger dieser E-Mail. Weitere Empfänger mit Komma trennen.</p>
</div>
<div class="long widget">
  <h3><label for="ctrl_cc">Cc</label></h3>
  <input type="text" name="cc" id="ctrl_cc" value="'.$email_cc.'" class="tl_text" onfocus="Backend.getScrollOffset()">
  <p class="tl_help tl_tip">Kopie-Empfänger dieser E-Mail. Weitere Empfänger mit Komma trennen.</p>
</div>
<div class="long widget">
  <h3><label for="ctrl_bcc">Bcc</label></h3>
  <input type="text" name="bcc" id="ctrl_bcc" value="'.$email_bcc.'" class="tl_text" onfocus="Backend.getScrollOffset()">
  <p class="tl_help tl_tip">Blindkopie-Empfänger dieser E-Mail. Weitere Empfänger mit Komma trennen.</p>
</div>
<div class="clear"></div>
</div>
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
'.($mail->sent_state ? '<span class="mandatory">Die E-Mail wurde bereits gesendet!</span>' : '<input type="submit" onclick="return confirm(\'Soll die E-Mail wirklich verschickt werden?\')" value="E-Mail versenden" accesskey="s" class="tl_submit" id="send">').'
</div>
</div>
</form>';
	}

	/**
	 * Baut die Vorschau einer Lizenz-E-Mail aus Vorlage und Datensatz.
	 *
	 * @param int    $mail_id    Datensatz-ID in tl_lizenzverwaltung_mails
	 * @param int    $trainer_id Datensatz-ID in tl_lizenzverwaltung_items
	 * @param int    $template   Datensatz-ID in tl_lizenzverwaltung_templates
	 * @param bool   $header     true liefert das vollständige HTML-Dokument,
	 *                           false nur den Inhalt des body-Elements
	 * @param string $css        Stilblock, der als Platzhalter ##css## eingesetzt wird
	 *
	 * @return string Der ersetzte Text. Enthält die Vorlage kein body-Element,
	 *                kommt bei `$header = false` eine leere Zeichenkette zurück.
	 */
	public function getPreview(int $mail_id, int $trainer_id, int $template, bool $header = true, string $css = ''): string
	{
		$tpl     = $this->fetchRow('tl_lizenzverwaltung_templates', $template);
		$mail    = $this->fetchRow('tl_lizenzverwaltung_mails', $mail_id);
		$trainer = $this->fetchLizenz($trainer_id);

		$arrTokens = array
		(
			'css'               => $css,
			'lizenz_art'        => $trainer->lizenz,
			'lizenz_nummer'     => $trainer->license_number_dosb,
			'lizenz_title'      => $mail->subject,
			'lizenz_vorname'    => $trainer->vorname,
			'lizenz_nachname'   => $trainer->name,
			'lizenz_geschlecht' => $trainer->geschlecht,
			'lizenz_content'    => $mail->content,
			'lizenz_signatur'   => $mail->signatur ? Config::get('lizenzverwaltung_mailsignatur') : '',
		);

		// [nbsp] und Co. zurückwandeln, bevor die Platzhalter greifen
		$content = Helper::replaceTokens(StringUtil::restoreBasicEntities((string) $tpl->template), $arrTokens);

		if ($header)
		{
			return $content;
		}

		return preg_match('/<body>(.*)<\/body>/s', $content, $matches) ? $matches[1] : '';
	}

	/**
	 * Prüft, ob eine E-Mail-Adresse gültig ist.
	 *
	 * Adressen dürfen in der Form "Name <adresse>" vorliegen; geprüft wird
	 * dann nur der Teil in den spitzen Klammern.
	 *
	 * @param string $email Die zu prüfende Adresse
	 *
	 * @return bool true, wenn die Adresse gültig ist
	 */
	public static function validateEmail(string $email): bool
	{
		preg_match('~(?:([^<]*?)\s*)?<(.*)>~', $email, $result);

		if (isset($result[2]))
		{
			$email = $result[2];
		}

		return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Zerlegt ein Eingabefeld in eine Liste von E-Mail-Adressen.
	 *
	 * @param mixed $value Der Feldwert; mehrere Adressen sind durch Komma getrennt
	 *
	 * @return array<int,string> Die Adressen ohne umschließende Leerzeichen;
	 *                           leere Einträge fallen weg, die Schlüssel sind
	 *                           lückenlos durchnummeriert
	 */
	private function parseAddresses($value): array
	{
		if (!$value)
		{
			return array();
		}

		return array_values(array_filter(array_map('trim', explode(',', html_entity_decode((string) $value)))));
	}

	/**
	 * Liest einen einzelnen Datensatz.
	 *
	 * @param string $table Tabellenname
	 * @param int    $id    Datensatz-ID
	 *
	 * @return object Das Zeilenobjekt; bei unbekannter ID liefert Contao ein
	 *                Objekt, dessen Felder alle null sind
	 */
	private function fetchRow(string $table, int $id): object
	{
		return Database::getInstance()->prepare("SELECT * FROM $table WHERE id = ?")
		                              ->limit(1)
		                              ->execute($id);
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

	/**
	 * Sucht die Lizenzurkunde eines Trainers im Lizenzordner.
	 *
	 * @param object $trainer  Zeilenobjekt mit dem Feld license_number_dosb
	 * @param string $suffix   Dateizusatz vor der Endung, leer für DIN A4
	 * @param bool   $anhaengen Ob die Datei laut Einstellung überhaupt
	 *                          mitgeschickt werden soll
	 *
	 * @return string|null Der absolute Pfad zur PDF-Datei, oder null wenn keine
	 *                     Lizenznummer vorliegt, kein Lizenzordner gewählt ist,
	 *                     die Datei fehlt oder sie nicht mitgeschickt werden soll
	 */
	private function findLizenzPdf(object $trainer, string $suffix, bool $anhaengen): ?string
	{
		if (!$anhaengen || !$trainer->license_number_dosb)
		{
			return null;
		}

		$uuid = $GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner'] ?? '';

		if (!$uuid)
		{
			return null;
		}

		$ordner = FilesModel::findByUuid($uuid);

		if (null === $ordner)
		{
			return null;
		}

		$datei = Helper::getProjectDir().'/'.$ordner->path.'/'.$trainer->license_number_dosb.$suffix.'.pdf';

		return file_exists($datei) ? $datei : null;
	}
}
