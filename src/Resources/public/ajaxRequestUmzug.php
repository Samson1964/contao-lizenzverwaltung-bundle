<?php
ini_set('display_errors', '1');

// MyClass.php

define('TL_SCRIPT', 'bundles/contaolizenzverwaltung/ajaxRequestUmzug.php');
define('TL_MODE', 'FE');

require($_SERVER['DOCUMENT_ROOT'].'/../system/initialize.php');

/**
 * Instantiate controller
 */
$ajax = new ajaxRequestUmzug();
$ajax->run();

class ajaxRequestUmzug
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

	public function run()
	{

		if(\Input::get('acid') == 'lizenzverwaltung')
		{

			// Anfügen der Methode für das Erstellen einer Umzugsanfrage
			$host = $this->host.'migration_request';

			// Datensatz laden
			$result = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung LEFT JOIN tl_lizenzverwaltung_items ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.id = ?")
			                                  ->limit(1)
			                                  ->execute(\Input::get('record'));

			// Auswerten
			if($result->numRows)
			{
				// Letztes Verlängerungsdatum ermitteln
				$verlaengerung = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerlaengerung($result->erwerb, $result->verlaengerungen);

				if($result->license_number_dosb)
				{
					$unterverbaende = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getUntergliederung($result->verband);

					$x = 1;
					foreach($unterverbaende as $unterverband)
					{
						// Datenpaket aufbereiten
						$data = array
						(
							'firstname'            => $result->vorname,
							'lastname'             => $result->name,
							'license_number_dosb'  => $result->license_number_dosb, // ID der DOSB-Lizenz
							'organisation_id'      => $unterverband,
						);
						//print_r($data);

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
							$errors = 'Curl error: ' . curl_error($process);
						}
						$header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
						$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE); // HTTP-Code der Abfrage
						$header = substr($response, 0, $header_size);
						$body = substr($response, $header_size);//json_decode(substr($response, $header_size));

						if(!$errors)
						{
							$data = json_decode($body);
                		
							if(is_object($data) && $httpCode == 200)
							{
								$httpText = 'OK';
							}
							else
							{
								$httpText = substr($body, 2, strlen($body) - 4);
							}
						}

                		
						// Sitzung anlegen/initialisieren
						$session = \Session::getInstance();
						$count = $session->get('lizenzverwaltung_counter') + 1;
						$session->set('lizenzverwaltung_counter', $count);

						$return = array
						(
							//'error' => $errors,
							//'response' => $response,
							'css_id' => '#export_'.$result->id,
							'color'  => $httpCode == 200 ? '#008000' : '#CA0000',
							'text'   => ' ('.$httpCode.' '.$httpText.')',
							'datum'  => '[<i>'.date('d.m.Y H:i:s').'</i>] '
						);
						//print_r($return);
            			
						echo json_encode($return);
						if($x == 1) break;
					}
				}

			}

		}
	}
}
