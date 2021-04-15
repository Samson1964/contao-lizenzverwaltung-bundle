<?php 

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Class Mailer
  */
class Mailer extends \Backend
{

	/**
	 * Versenden einer E-Mail
	 */

	public function send(\DataContainer $dc)
	{
		$css = '<style>
	* { font-family:Calibri,Verdana,sans-serif,Arial; font-size:16px; }
</style>';
		
		// E-Mail-Datensatz einlesen
		$mail = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_mails WHERE id = ?")
		                                ->execute($dc->id);
		// Lizenz und Personen-Datensatz einlesen
		$trainer = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                   ->execute($mail->pid);
		// Referenten-Datensätze einlesen
		$referenten = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE verband = ? AND published = ?")
		                                      ->execute($trainer->verband, 1);
		// Datensätze mit DSB-Referenten einlesen
		$dsbreferenten = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE verband = ? AND published = ?")
		                                         ->execute('S', 1);

		$preview = $this->getPreview($dc->id, $mail->pid, $mail->template); // HTML-Vorschau erstellen
		$preview_css = $this->getPreview($dc->id, $mail->pid, $mail->template, true, $css); // HTML/CSS-Version erstellen
		$preview_body = $this->getPreview($dc->id, $mail->pid, $mail->template, false); // Body-Vorschau erstellen

		$lizenzordner = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner']);

		// Lizenz-PDF DIN A4 vorhanden?
		$lizenzfilenameA4 = false;
		if($trainer->license_number_dosb)
		{
			$lizenzfilenameA4 = TL_ROOT.'/'.$lizenzordner->path.'/'.$trainer->license_number_dosb.'.pdf';
			if(!$mail->insertLizenz || !file_exists($lizenzfilenameA4))
			{
				$lizenzfilenameA4 = false;
			}
		}

		// Lizenz-PDF Karte vorhanden?
		$lizenzfilenameCard = false;
		if($trainer->license_number_dosb)
		{
			$lizenzfilenameCard = TL_ROOT.'/'.$lizenzordner->path.'/'.$trainer->license_number_dosb.'-card.pdf';
			if(!$mail->insertLizenzCard || !file_exists($lizenzfilenameCard))
			{
				$lizenzfilenameCard = false;
			}
		}

		// E-Mail versenden
		if(\Input::get('token') != '' && \Input::get('token') == $this->Session->get('tl_lizenzverwaltung_send'))
		{
			
			$this->Session->set('tl_lizenzverwaltung_send', null); 
			$objEmail = new \Email();
			
			if($lizenzfilenameA4) $objEmail->attachFile($lizenzfilenameA4); // Lizenz-PDF DIN A4 anhängen
			if($lizenzfilenameCard) $objEmail->attachFile($lizenzfilenameCard); // Lizenz-PDF Karte anhängen
			
			// Absender "Name <email>" in ein Array $arrFrom aufteilen
			preg_match('~(?:([^<]*?)\s*)?<(.*)>~', LIZENZVERWALTUNG_ABSENDER, $arrFrom);
			
			// Empfänger-Adressen in ein Array packen
			$to = explode(',', html_entity_decode(\Input::get('an')));
			$cc = explode(',', html_entity_decode(\Input::get('cc')));
			$bcc = explode(',', html_entity_decode(\Input::get('bcc')));

			// Führende und abschließende Leerzeichen entfernen
			$to = array_map('trim', $to);
			$cc = array_map('trim', $cc);
			$bcc = array_map('trim', $bcc);

			// Check whether the alias exists
			//if(!$test)
			//{
			//	throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], 'Fehler'));
			//}

			$objEmail->from = $arrFrom[2];
			$objEmail->fromName = $arrFrom[1];
			$objEmail->subject = $mail->subject;
			$objEmail->logFile = 'lizenzverwaltung_email.log';
			$objEmail->html = $preview_css; 
			if($cc[0]) $objEmail->sendCc($cc); 
			if($bcc[0]) $objEmail->sendBcc($bcc); 
			$status = $objEmail->sendTo($to); 
			if($status)
			{
				// Versanddatum in Datenbank eintragen
				$set = array
				(
					'sent_date'  => time(),
					'sent_state' => 1,
					'sent_text'  => $preview_body
				);
				$trainer = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_mails %s WHERE id = ?")
				                                   ->set($set)
				                                   ->execute($dc->id);
				// Email-Versand bestätigen und weiterleiten
				\Message::addConfirmation('E-Mail versendet'); 
				// Zurücklink generieren, ab C4 ist das ein symbolischer Link zu "contao"
				if (version_compare(VERSION, '4.0', '>='))
				{
					$backlink = \System::getContainer()->get('router')->generate('contao_backend');
				}
				else
				{
					$backlink = 'contao/main.php';
				}
				\Controller::redirect($backlink.'?do='.\Input::get('do').'&table='.\Input::get('table').'&id='.$mail->pid);
			}
			exit;
		}
		
		// E-Mail-Empfänger festlegen
		// 1. Lizenzinhaber
		$trainer->email ? $email_an = htmlentities($trainer->vorname.' '.$trainer->name.' <'.$trainer->email.'>') : $email_an = '';
		// 2. Referenten, die informiert werden wollen
		if($mail->copyVerband && $referenten->numRows > 0)
		{
			$email_cc = '';
			while($referenten->next())
			{
				$email_cc .= htmlentities($referenten->vorname.' '.$referenten->nachname.' <'.$referenten->email.'>, ');
			}
			if($email_cc) $email_cc = substr($email_cc, 0, -2); // Letztes ", " entfernen
		}
		// 3. Kopie an Verantwortliche in DSB-GS und andere DSB-Referenten
		if($mail->copyDSB)
		{
			$email_bcc = htmlentities(LIZENZVERWALTUNG_ABSENDER);
			if($dsbreferenten->numRows > 0)
			{
				while($dsbreferenten->next())
				{
					$email_bcc .= htmlentities(', '.$dsbreferenten->vorname.' '.$dsbreferenten->nachname.' <'.$dsbreferenten->email.'>');
				}
			}
		}

		$strToken = md5(uniqid(mt_rand(), true));
		$this->Session->set('tl_lizenzverwaltung_send', $strToken); 

		return
		'<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
'.\Message::generate().'
<form action="'.TL_SCRIPT.'" id="tl_lizenzverwaltung_send" class="tl_form" method="get">
<div class="tl_formbody_edit tl_lizenzverwaltung_send">
<input type="hidden" name="do" value="' . \Input::get('do') . '">
<input type="hidden" name="table" value="' . \Input::get('table') . '">
<input type="hidden" name="key" value="' . \Input::get('key') . '">
<input type="hidden" name="id" value="' . \Input::get('id') . '">
<input type="hidden" name="token" value="' . $strToken . '">
<div class="tl_preview">
<table class="prev_header">
  <tr class="row_0">
    <td class="col_0"><b>Absender:</b></td>
    <td class="col_1">' . htmlentities(LIZENZVERWALTUNG_ABSENDER) . '</td>
  </tr>
  <tr class="row_1">
    <td class="col_0"><b>Betreff:</b></td>
    <td class="col_1">' . $mail->subject . '</td>
  </tr>
  <tr class="row_2">
    <td class="col_0"><b>E-Mail-Template:</b></td>
    <td class="col_1">' . $mail->template . '</td>
  </tr>
</table>
</div>
<div class="tl_preview">' .$preview_body. '</div>

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


	public function getPreview($mail_id, $trainer_id, $template, $header = true, $css = false)
	{
		$objTemplate = new \BackendTemplate($template);
		// Mail-Datensatz einlesen
		$mail = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_mails WHERE id = ?")
		                                ->execute($mail_id);
		// Lizenz- und Personen-Datensatz einlesen
		$trainer = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                   ->execute($trainer_id);

		$objTemplate->setData($trainer->row()); // Trainer-Daten in Template-Objekt eintragen
		$objTemplate->title = $mail->subject;
		$objTemplate->charset = \Config::get('characterSet');
		$objTemplate->css = $css;
		$objTemplate->content = $mail->content;
		$content = $objTemplate->parse();
		$content = \StringUtil::restoreBasicEntities($content); // [nbsp] und Co. ersetzen
		
		if($header)
		{
			// Mit HTML-Header zurückgeben
			return $content;
		}
		else
		{
			// Nur Body-Tag zurückgeben
			preg_match('/<body>(.*)<\/body>/s', $content, $matches); // Body extrahieren
			return $matches[1];
		}

	}
	
}
