<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(0);


// http://development.schachbund.de/bundles/contaolizenzverwaltung/migration_c3-to-c4.php
define('TL_SCRIPT', 'MIGRATION_Trainerlizenzen');
define('TL_MODE', 'FE');

include($_SERVER['DOCUMENT_ROOT'].'/../system/initialize.php');

// Alle Lizenzen laden
$objLizenz = Contao\Database::getInstance()->query('SELECT * FROM tl_trainerlizenzen');
if($objLizenz->numRows > 0)
{
	while($objLizenz->next())
	{
		// Person anhand Vor- und Nachname suchen
		$objPerson = Contao\Database::getInstance()->prepare('SELECT * FROM tl_lizenzverwaltung WHERE name = ? AND vorname = ?')
		                                           ->execute($objLizenz->name, $objLizenz->vorname);
		if($objPerson->numRows > 0)
		{
			// Person bereits vorhanden
			$id = $objPerson->id;
			// Unterschiede finden
			$unterschiede = '';
			if($objLizenz->titel != $objPerson->titel) $unterschiede .= 'Abweichender Titel: <b>'.$objLizenz->titel.'</b><br>';
			if($objLizenz->geburtstag != $objPerson->geburtstag) $unterschiede .= 'Abweichender Geburtstag: <b>'.$objLizenz->geburtstag.'</b><br>';
			if($objLizenz->geschlecht != $objPerson->geschlecht) $unterschiede .= 'Abweichendes Geschlecht: <b>'.$objLizenz->geschlecht.'</b><br>';
			if($objLizenz->strasse != $objPerson->strasse) $unterschiede .= 'Abweichendes Straße: <b>'.$objLizenz->strasse.'</b><br>';
			if($objLizenz->plz != $objPerson->plz) $unterschiede .= 'Abweichende PLZ: <b>'.$objLizenz->plz.'</b><br>';
			if($objLizenz->ort != $objPerson->ort) $unterschiede .= 'Abweichender Ort: <b>'.$objLizenz->ort.'</b><br>';
			if($objLizenz->email != $objPerson->email) $unterschiede .= 'Abweichende E-Mail: <b>'.$objLizenz->email.'</b><br>';
			if($objLizenz->telefon != $objPerson->telefon) $unterschiede .= 'Abweichender Titel: <b>'.$objLizenz->telefon.'</b><br>';
			if($objLizenz->addEnclosure != $objPerson->addEnclosure) $unterschiede .= 'Abweichender addEnclosure: <b>'.$objLizenz->addEnclosure.'</b><br>';
			if($objLizenz->enclosure != $objPerson->enclosure) $unterschiede .= 'Abweichender enclosure: <b>'.$objLizenz->enclosure.'</b><br>';
			if($objLizenz->enclosureInfo != $objPerson->enclosureInfo) $unterschiede .= 'Abweichende enclosureInfo: <b>'.$objLizenz->enclosureInfo.'</b><br>';
			if($objLizenz->bemerkung != $objPerson->bemerkung) $unterschiede .= 'Abweichende Bemerkung: <b>'.$objLizenz->bemerkung.'</b><br>';
			if($objLizenz->published != $objPerson->published) $unterschiede .= 'Abweichende Aktivierung: <b>'.$objLizenz->published.'</b><br>';
			if($unterschiede)
			{
				$unterschiede .= '[Datensatz Nr. '.$objLizenz->id.']<hr>';
				// Datensatz updaten
				$set = array
				(
					'migrationInfo'        => $unterschiede.$objPerson->migrationInfo
				);
				$id = \Database::getInstance()->prepare('UPDATE tl_lizenzverwaltung %s WHERE id = ?')
				                              ->set($set)
				                              ->execute($objPerson->id)
				                              ->insertId;
			}
		}
		else
		{
			// Person noch nicht vorhanden, dann neu anlegen
			$set = array
			(
				'tstamp'               => $objLizenz->tstamp,
				'vorname'              => $objLizenz->vorname,
				'name'                 => $objLizenz->name,
				'titel'                => $objLizenz->titel,
				'geburtstag'           => $objLizenz->geburtstag,
				'geschlecht'           => $objLizenz->geschlecht,
				'strasse'              => $objLizenz->strasse,
				'plz'                  => $objLizenz->plz,
				'ort'                  => $objLizenz->ort,
				'email'                => $objLizenz->email,
				'telefon'              => $objLizenz->telefon,
				'addEnclosure'         => $objLizenz->addEnclosure,
				'enclosure'            => $objLizenz->enclosure,
				'bemerkung'            => $objLizenz->bemerkung,
				'published'            => $objLizenz->published
			);
			$id = \Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung %s')
			                              ->set($set)
			                              ->execute()
			                              ->insertId;
		}

		// Lizenzdatensatz anlegen
		$set = array
		(
			'pid'                  => $id,
			'tstamp'               => $objLizenz->tstamp,
			'license_number_dosb'  => $objLizenz->license_number_dosb,
			'lid'                  => $objLizenz->lid,
			'dosb_tstamp'          => $objLizenz->dosb_tstamp,
			'dosb_code'            => $objLizenz->dosb_code,
			'dosb_antwort'         => $objLizenz->dosb_antwort,
			'dosb_pdf_tstamp'      => $objLizenz->dosb_pdf_tstamp,
			'dosb_pdf_code'        => $objLizenz->dosb_pdf_code,
			'dosb_pdf_antwort'     => $objLizenz->dosb_pdf_antwort,
			'dosb_pdfcard_tstamp'  => $objLizenz->dosb_pdfcard_tstamp,
			'dosb_pdfcard_code'    => $objLizenz->dosb_pdfcard_code,
			'dosb_pdfcard_antwort' => $objLizenz->dosb_pdfcard_antwort,
			'marker'               => $objLizenz->marker,
			'verband'              => $objLizenz->verband,
			'lizenznummer'         => $objLizenz->lizenznummer,
			'lizenz'               => $objLizenz->lizenz,
			'erwerb'               => $objLizenz->erwerb,
			'verlaengerungen'      => $objLizenz->verlaengerungen,
			'gueltigkeit'          => $objLizenz->gueltigkeit,
			'codex'                => $objLizenz->codex,
			'codex_date'           => $objLizenz->codex_date,
			'help'                 => $objLizenz->help,
			'help_date'            => $objLizenz->help_date,
			'letzteAenderung'      => $objLizenz->letzteAenderung,
			'addEnclosure'         => $objLizenz->addEnclosure,
			'enclosure'            => $objLizenz->enclosure,
			'bemerkung'            => $objLizenz->bemerkung,
			'published'            => $objLizenz->published
		);
		$objRecord = \Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung_items %s')
		                                     ->set($set)
		                                     ->execute();
	}
}

exit;

echo "<pre>";
print_r($personen);
echo "</pre>";

// Alle Personen durchgehen und Datensätze in tl_trainerlizenzen_items anlegen
foreach($personen as $person)
{
	for($x = 0; $x < count($person); $x++)
	{
		// Datensatz laden
		$objLizenz = Contao\Database::getInstance()->prepare('SELECT * FROM tl_trainerlizenzen WHERE id = ?')
		                                           ->execute($person[$x]);
		// Verlängerungen zusammenfassen
		//$verlaengerungen = array();
		//if($objLizenz->verlaengerung1) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung1);
		//if($objLizenz->verlaengerung2) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung2);
		//if($objLizenz->verlaengerung3) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung3);
		//if($objLizenz->verlaengerung4) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung4);
		//if($objLizenz->verlaengerung5) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung5);
		//if($objLizenz->verlaengerung6) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung6);
		//if($objLizenz->verlaengerung7) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung7);
		//if($objLizenz->verlaengerung8) $verlaengerungen[] = array('datum' => $objLizenz->verlaengerung8);
		// Neuen Datensatz in tl_trainerlizenzen_items anlegen
		$set = array
		(
			'pid'                  => $person[0],
			'tstamp'               => $objLizenz->tstamp,
			'license_number_dosb'  => $objLizenz->license_number_dosb,
			'lid'                  => $objLizenz->lid,
			'dosb_tstamp'          => $objLizenz->dosb_tstamp,
			'dosb_code'            => $objLizenz->dosb_code,
			'dosb_antwort'         => $objLizenz->dosb_antwort,
			'dosb_pdf_tstamp'      => $objLizenz->dosb_pdf_tstamp,
			'dosb_pdf_code'        => $objLizenz->dosb_pdf_code,
			'dosb_pdf_antwort'     => $objLizenz->dosb_pdf_antwort,
			'dosb_pdfcard_tstamp'  => $objLizenz->dosb_pdfcard_tstamp,
			'dosb_pdfcard_code'    => $objLizenz->dosb_pdfcard_code,
			'dosb_pdfcard_antwort' => $objLizenz->dosb_pdfcard_antwort,
			'marker'               => $objLizenz->marker,
			'verband'              => $objLizenz->verband,
			'lizenznummer'         => $objLizenz->lizenznummer,
			'lizenz'               => $objLizenz->lizenz,
			'erwerb'               => $objLizenz->erwerb,
			'verlaengerungen'      => $objLizenz->verlaengerungen,
			'gueltigkeit'          => $objLizenz->gueltigkeit,
			'codex'                => $objLizenz->codex,
			'codex_date'           => $objLizenz->codex_date,
			'help'                 => $objLizenz->help,
			'help_date'            => $objLizenz->help_date,
			'letzteAenderung'      => $objLizenz->letzteAenderung,
			'addEnclosure'         => $objLizenz->addEnclosure,
			'enclosure'            => $objLizenz->enclosure,
			'bemerkung'            => $objLizenz->bemerkung,
			'published'            => $objLizenz->published
		);
		$objRecord = \Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung_items %s')
		                                     ->set($set)
		                                     ->execute();
	}
}
