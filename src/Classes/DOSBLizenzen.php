<?php

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Class dsb_trainerlizenzImport
  */
class DOSBLizenzen extends \Backend
{

	var $organization;
	var $host;
	var $username;
	var $password;
	var $training_course_id;

	function __construct()
	{
		$this->organization = '1093';
		$this->host = LIMS_HOST;
		$this->username = LIMS_USERNAME;
		$this->password = LIMS_PASSWORD;
		$this->training_course_id = array
		(
			'A'              => 515, // T-A/L > Schach
			'B'              => 514, // T-B/L > Schach
			'C'              => 513, // T-C/L > Schach
			'C-B'            => 512, // T-C/B > Schach
			'C-Sonderlizenz' => 512, // T-C/B > Schach
			'F'              => 512, // fiktiv, nicht angelegt beim DOSB
			'F/C'            => 512, // fiktiv, nicht angelegt beim DOSB
			'J'              => 512, // fiktiv, nicht angelegt beim DOSB
			'AB-Z'           => 49337, // Ausbilder-Zertifikat
		);
	}

	/**
	 * Erstellen/Verlängern einer Lizenz
	 */

	public function getLizenz()
	{
		// Datensatz-ID der Lizenz
		$id = \Input::get('id');

		// Lizenz- und Personen-Datensatz einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                  ->execute($id);

		// Auswerten
		if($result->numRows)
		{
			// Anfügen der Methode für das Erstellen/Aktualisieren einer Lizenz
			$host = $this->host.'request';

			// Letztes Verlängerungsdatum ermitteln
			$verlaengerung = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerlaengerung($result->erwerb, $result->verlaengerungen);
			//if(!$verlaengerung) $verlaengerung = $result->erwerb;

			// Datenpaket aufbereiten
			$data = array
			(
				'firstname'          => $result->vorname,
				'lastname'           => $result->name,
				'academic_title'     => $result->titel,
				'birthdate'          => $result->geburtstag + 7200, // Geburtstag als Unixzeit verlangt
				'gender'             => $result->geschlecht,
				'street'             => $result->strasse,
				'city'               => $result->ort,
				'postal'             => $result->plz,
				'mail'               => $result->email,
				'training_course_id' => $this->training_course_id[$result->lizenz], // Ausbildungsgang
				'valid_until'        => $result->gueltigkeit, // Lizenz gültig bis als Unixzeit
				'issue_date'         => $verlaengerung, // Verlängerungsdatum
				'issue_place'        => 'Berlin', // Ausstellungsort
				'honor_code'         => (int)$result->codex, // Ehrenkodex
				'honor_code_date'    => $result->codex_date, // Datum Ehrenkodex
				'first_aid'          => (int)$result->help, // Erste-Hilfe-Ausbildung
				'first_aid_date'     => $result->help_date, // Datum der Erste-Hilfe-Ausbildung
			);
			// Restliche Werte ergänzen
			if($result->license_number_dosb)
			{
				// Existierende Lizenznummer
				$data['license_number_dosb'] = $result->license_number_dosb; // Aktive DOSB-Lizenznummer
			}
			else
			{
				// Nicht existierende Lizenznummer, dann Org-Nummer/Ausstelldatum übermitteln
				$data['organisation_id']     = 1093; // Organisationsnummer DSB
				$data['first_issue_date']    = $result->erwerb; // Erstausstellungsdatum
			}
			//echo $host;
		}

		// Logfile-Daten anlegen
		$log = "Lizenzdaten-Transfer:\n";
		$log .= print_r($data, true);

		$additionalHeaders = '';
		$process = curl_init($host);

		//hier ist auch noch application/xml möglich
		curl_setopt($process, CURLOPT_HTTPHEADER, array
		(
		  'Accept: application/json',
		  $additionalHeaders
		));
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		//nur für test zwecke
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE);
		//request ausführen
		$response = curl_exec($process);

		$errors = NULL;
		if (curl_errno($process))
		{
			$errors=  'Curl error: ' . curl_error($process);
		}

		$header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
		$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE); // HTTP-Code der Abfrage
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);//json_decode(substr($response, $header_size));

		if($errors)
		{
			// Fehlermeldung in Datenbank eintragen
		}
		else
		{
			$data = json_decode($body);

			if(is_object($data) && $httpCode == 200)
			{
				// Datensatz aktualisieren
				$set = array(
					'license_number_dosb' => $data->license_number_dosb,
					'lid'                 => $data->lid,
				);
				$result = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
				                                  ->set($set)
				                                  ->execute($id);
				$httpText = 'OK';
			}
			else
			{
				$httpText = substr($body, 2, strlen($body) - 4);
			}
			// Abrufinformationen ergänzen
			$set = array(
				'dosb_tstamp'         => time(),
				'dosb_code'           => $httpCode,
				'dosb_antwort'        => $httpText,
			);
			$result = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
			                                  ->set($set)
			                                  ->execute($id);
		}

		$log .= "Response Body: $body\n";
		$log .= "CURL Errors: $errors";
		log_message($log, 'lizenzverwaltung.log');

		// Zurück zur Seite
		\Controller::redirect(str_replace('&key=getLizenz', '&act=edit', \Environment::get('request')));

	}

	/**
	 * Abrufen einer Lizenz als PDF im Format DIN A4
	 */

	public function getLizenzPDF()
	{
		// Datensatz-ID des Trainers
		$id = \Input::get('id');

		// Lizenz- und Personen-Datensatz einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                  ->execute($id);

		$lizenzordner = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner']);

		// Auswerten
		if($result->numRows)
		{
			// Anfügen der Methode für das Abrufen einer Lizenz als PDF
			$host = $this->host.'download/'.urlencode($result->license_number_dosb);

			$process = curl_init($host);

			//hier ist auch noch application/xml möglich
			curl_setopt($process, CURLOPT_HTTPHEADER, array(
			  'Accept: application/json'
			));
			curl_setopt($process, CURLOPT_HEADER, 1);
			curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
			curl_setopt($process, CURLOPT_TIMEOUT, 60);
			curl_setopt($process, CURLOPT_POST, 1);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			//nur für test zwecke
			curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE);

			//Request ausfuehren
			$response = curl_exec($process);

			$errors = NULL;
			if(curl_errno($process))
			{
				$errors =  'Curl error: ' . curl_error($process);
			}

			$header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
			$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE); // HTTP-Code der Abfrage
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);//json_decode(substr($response, $header_size));

			if($httpCode == 200 && !$errors)
			{
				// Schreiben der Daten in eine PDF
				$filename = TL_ROOT.'/'.$lizenzordner->path.'/'.$result->license_number_dosb.'.pdf';
				file_put_contents($filename, $body);
				$httpText = 'OK';
			}
			else
			{
				$httpText = substr($body, 2, strlen($body) - 4);
			}

			// Abrufinformationen ergänzen
			$set = array(
				'dosb_pdf_tstamp'         => time(),
				'dosb_pdf_code'           => $httpCode,
				'dosb_pdf_antwort'        => $httpText,
			);
			$result = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
			                                  ->set($set)
			                                  ->execute($id);

		}

		$log = "PDF-Abruf:\n";
		$log .= "Host: $host\n";
		$log .= "Response Body: $httpCode $httpText\n";
		$log .= "CURL Errors: $errors";
		log_message($log, 'trainerlizenzen.log');

		// Zurück zur Seite
		\Controller::redirect(str_replace('&key=getLizenzPDF', '&act=edit', \Environment::get('request')));
	}

	/**
	 * Abrufen einer Lizenz als PDF im Format Card
	 */

	public function getLizenzPDFCard()
	{
		// Datensatz-ID des Trainers
		$id = \Input::get('id');

		// Lizenz- und Personen-Datensatz einlesen
		$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
		                                  ->execute($id);

		$lizenzordner = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner']);

		// Auswerten
		if($result->numRows)
		{
			// Anfügen der Methode für das Abrufen einer Lizenz als PDF-Karte
			$host = $this->host.'download/'.urlencode($result->license_number_dosb);

			$options = array('format' => 'card'); //format = card, dina4, signet

			$process = curl_init($host);

			//hier ist auch noch application/xml möglich
			curl_setopt($process, CURLOPT_HTTPHEADER, array(
			  'Accept: application/json'
			));
			curl_setopt($process, CURLOPT_HEADER, 1);
			curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
			curl_setopt($process, CURLOPT_TIMEOUT, 60);
			curl_setopt($process, CURLOPT_POST, 1);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($process, CURLOPT_POSTFIELDS, $options);
			//nur für test zwecke
			curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE);

			//Request ausfuehren
			$response = curl_exec($process);

			$errors = NULL;
			if(curl_errno($process))
			{
				$errors =  'Curl error: ' . curl_error($process);
			}

			$header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
			$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE); // HTTP-Code der Abfrage
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);//json_decode(substr($response, $header_size));

			if($httpCode == 200 && !$errors)
			{
				// Schreiben der Daten in eine PDF
				$filename = TL_ROOT.'/'.$lizenzordner->path.'/'.$result->license_number_dosb.'-card.pdf';
				file_put_contents($filename, $body);
				$httpText = 'OK';
			}
			else
			{
				$httpText = substr($body, 2, strlen($body) - 4);
			}

			// Abrufinformationen ergänzen
			$set = array(
				'dosb_pdfcard_tstamp'         => time(),
				'dosb_pdfcard_code'           => $httpCode,
				'dosb_pdfcard_antwort'        => $httpText,
			);
			$result = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_items %s WHERE id=?")
			                                  ->set($set)
			                                  ->execute($id);

		}

		$log = "PDF-Cardabruf:\n";
		$log .= "Host: $host\n";
		$log .= "Response Body: $httpCode $httpText\n";
		$log .= "CURL Errors: $errors";
		log_message($log, 'trainerlizenzen.log');

		// Zurück zur Seite
		\Controller::redirect(str_replace('&key=getLizenzPDFCard', '&act=edit', \Environment::get('request')));

	}

	/**
	 * Exportiert alle noch nicht übertragenen Lizenzen zum DOSB
	 */
	public function exportToDOSB()
	{

		$start = \Input::get('start');
		if($start)
		{
			// jQuery einbinden
			$GLOBALS['TL_JAVASCRIPT'][] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js';

			// Export starten
			// Datensätze einlesen, bei der die Lizenz noch aktiv ist (größer/gleich aktuelles Datum)
			$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.gueltigkeit >= ? AND tl_lizenzverwaltung_items.published = ? AND (tl_lizenzverwaltung_items.letzteAenderung > tl_lizenzverwaltung_items.dosb_tstamp OR tl_lizenzverwaltung_items.dosb_code <> 200) ORDER BY tl_lizenzverwaltung.id")
			                                  ->execute(time(), 1);
			$arrLizenzen = NULL;
			$arrLizenzen = is_array($arrLizenzen) ? array_intersect($arrLizenzen, $result->fetchEach('id')) : $result->fetchEach('id');
			$records = implode(',', $arrLizenzen);

			// Datensätze in die Ausgabe schreiben
			if($result->numRows)
			{
				$content .= '<div id="dosb_export_status">[<i>'.date('d.m.Y H:i:s').'</i>] <b>'.$result->numRows.' Datensätze sind zu exportieren ...</b><br>';
				$result->reset();
				while($result->next())
				{
					$content .= '<span class="item" id="export_'.$result->id.'"> ID '.$result->id.' '.$result->vorname.' '.$result->name.' ...</span><br>';
				}
			}
			else
			{
				$content .= '<div id="dosb_export_status">[<i>'.date('d.m.Y H:i:s').'</i>] <b>Keine Datensätze zum Exportieren gefunden.</b><br>';
			}

			// Zurücklink generieren
			$backlink = str_replace('&key=exportDOSB&start=1', '', \Environment::get('request'));
			$content .= '<div style="margin-top:30px;"><a href="'.$backlink.'" class="dosb_button_mini">Zurück zur Lizenzverwaltung</a></div>';

			$content .= '</div>';
			$content .= '<script>'."\n";
			$content .= 'var records = ['.$records.'];'."\n";
			$content .= 'for(let i=0; i<records.length; i++) {'."\n";
			$content .= "  $.get('bundles/contaolizenzverwaltung/ajaxRequest.php?acid=lizenzverwaltung&record='+records[i], function (data)"."\n";
			$content .= '  {'."\n";
			$content .= '     var item = JSON.parse(data);';
			$content .= '     $(item.css_id).prepend(item.datum);'."\n";
			$content .= '     $(item.css_id).append(item.text);'."\n";
			$content .= '     $(item.css_id).css("color", item.color);'."\n";
			$content .= '     $(item.css_id).fadeIn("slow")'."\n";
			$content .= '  })'."\n";
			$content .= '}'."\n";
			$content .= '</script>'."\n";

			// Sitzung anlegen/initialisieren
			$session = \Session::getInstance();
			$session->set('lizenzverwaltung_counter', 0);
			$session->set('lizenzverwaltung_max', count($arrLizenzen));

		}
		else
		{
			// Link generieren
			$startlink = \Controller::addToUrl('start=1&rt='.REQUEST_TOKEN); // start und Token hinzufügen

			$content .= '<div id="dosb_export_status">';
			$content .= '<h2 class="sub_headline">Aktive Lizenzen zum DOSB exportieren</h2>';
			$content .= '<div class="tl_submit_container">';
			$content .= '<a href="'.$startlink.'" class="dosb_button_mini">Export starten</a>';
			$content .= '</div>';
			$content .= '</div>';
		}
		return $content;
	}

	/**
	 * Wandelt JJJJMMTT in Unixzeit um
	 * @param int
	 * @return int
	 */

	public function dateToUnix($value)
	{
		$jahr = 0 + substr($value, 0, 4);
		$monat = 0 + ltrim(substr($value, 4, 2),0);
		$tag = 0 + ltrim(substr($value, 6, 2),0);
		return mktime(0, 0, 0, $monat, $tag, $jahr);
	}

}
