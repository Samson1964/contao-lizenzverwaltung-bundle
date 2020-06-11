<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(0);

// http://development.schachbund.de/bundles/contaolizenzverwaltung/migration_c3-to-c4.php
define('TL_SCRIPT', 'MIGRATION_Trainerlizenzen');
define('TL_MODE', 'FE');

include($_SERVER['DOCUMENT_ROOT'].'/../system/initialize.php');

// Alle Lizenzen laden
$objLizenz = Contao\Database::getInstance()->prepare('SELECT * FROM tl_trainerlizenzen')
                                           ->execute();
// Personen in Array übertragen
$Person = array();
$id = 1;
if($objLizenz->numRows)
{
	while($objLizenz->next())
	{
		$key = $objLizenz->name.'-'.$objLizenz->vorname;
		if(!$Person[$key])
		{
			// Person existiert noch nicht
			$Person[$key]['Stammdaten'] = array
			(
				'id'                 => $id,
				'tstamp'             => $objLizenz->tstamp,
				'vorname'            => $objLizenz->vorname,
				'name'               => $objLizenz->name,
				'titel'              => $objLizenz->titel,
				'geschlecht'         => $objLizenz->geschlecht,
				'geburtstag'         => $objLizenz->geburtstag,
				'strasse'            => $objLizenz->strasse,
				'plz'                => $objLizenz->plz,
				'ort'                => $objLizenz->ort,
				'email'              => $objLizenz->email,
				'telefon'            => $objLizenz->telefon,
				'published'          => 1
			);
			$id++;
		}
		// Lizenz anlegen
		$Person[$key]['Lizenzen'][] = array
		(
			'id'                   => $objLizenz->id,
			'pid'                  => $Person[$key]['Stammdaten']['id'],
			'tstamp'               => $objLizenz->tstamp,
			'verband'              => $objLizenz->verband,
			'lizenznummer'         => $objLizenz->lizenznummer,
			'lizenz'               => $objLizenz->lizenz,
			'erwerb'               => $objLizenz->erwerb,
			//'verlaengerung1'       => $objLizenz->verlaengerung1,
			//'verlaengerung2'       => $objLizenz->verlaengerung2,
			//'verlaengerung3'       => $objLizenz->verlaengerung3,
			//'verlaengerung4'       => $objLizenz->verlaengerung4,
			//'verlaengerung5'       => $objLizenz->verlaengerung5,
			//'verlaengerung6'       => $objLizenz->verlaengerung6,
			//'verlaengerung7'       => $objLizenz->verlaengerung7,
			//'verlaengerung8'       => $objLizenz->verlaengerung8,
			'verlaengerungen'      => $objLizenz->verlaengerungen,
			'gueltigkeit'          => $objLizenz->gueltigkeit,
			'letzteAenderung'      => $objLizenz->letzteAenderung,
			'bemerkung'            => $objLizenz->bemerkung,
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
			'codex'                => $objLizenz->codex,
			'codex_date'           => $objLizenz->codex_date,
			'help'                 => $objLizenz->help,
			'help_date'            => $objLizenz->help_date,
			'marker'               => $objLizenz->marker,
			'enclosure'            => $objLizenz->enclosure,
			'addEnclosure'         => $objLizenz->addEnclosure,
			'published'            => $objLizenz->published
		);
	}
}

echo "<pre>";
print_r($Person);
echo "<pre>";
//exit;

// Personen und Lizenzen in Datenbank eintragen
foreach($Person as $item)
{
	// Person übertragen
	$set = array
	(
		'id'                 => $item['Stammdaten']['id'],
		'tstamp'             => $item['Stammdaten']['tstamp'],
		'vorname'            => $item['Stammdaten']['vorname'],
		'name'               => $item['Stammdaten']['name'],
		'titel'              => $item['Stammdaten']['titel'],
		'geburtstag'         => $item['Stammdaten']['geburtstag'],
		'geschlecht'         => $item['Stammdaten']['geschlecht'],
		'strasse'            => $item['Stammdaten']['strasse'],
		'plz'                => $item['Stammdaten']['plz'],
		'ort'                => $item['Stammdaten']['ort'],
		'email'              => $item['Stammdaten']['email'],
		'telefon'            => $item['Stammdaten']['telefon'],
		'published'          => 1
	);
	\Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung %s')
	                        ->set($set)
	                        ->execute();

	foreach($item['Lizenzen'] as $lizenz)
	{
		// Lizenz übertragen
		$set = array
		(
			'id'                   => $lizenz['id'],
			'pid'                  => $lizenz['pid'],
			'tstamp'               => $lizenz['tstamp'],
			'verband'              => $lizenz['verband'],
			'lizenznummer'         => $lizenz['lizenznummer'],
			'lizenz'               => $lizenz['lizenz'],
			'erwerb'               => $lizenz['erwerb'],
			'verlaengerungen'      => $lizenz['verlaengerungen'],
			'gueltigkeit'          => $lizenz['gueltigkeit'],
			'letzteAenderung'      => $lizenz['letzteAenderung'],
			'bemerkung'            => $lizenz['bemerkung'],
			'license_number_dosb'  => $lizenz['license_number_dosb'],
			'lid'                  => $lizenz['lid'],
			'dosb_tstamp'          => $lizenz['dosb_tstamp'],
			'dosb_code'            => $lizenz['dosb_code'],
			'dosb_antwort'         => $lizenz['dosb_antwort'],
			'dosb_pdf_tstamp'      => $lizenz['dosb_pdf_tstamp'],
			'dosb_pdf_code'        => $lizenz['dosb_pdf_code'],
			'dosb_pdf_antwort'     => $lizenz['dosb_pdf_antwort'],
			'dosb_pdfcard_tstamp'  => $lizenz['dosb_pdfcard_tstamp'],
			'dosb_pdfcard_code'    => $lizenz['dosb_pdfcard_code'],
			'dosb_pdfcard_antwort' => $lizenz['dosb_pdfcard_antwort'],
			'codex'                => $lizenz['codex'],
			'codex_date'           => $lizenz['codex_date'],
			'help'                 => $lizenz['help'],
			'help_date'            => $lizenz['help_date'],
			'marker'               => $lizenz['marker'],
			'enclosure'            => $lizenz['enclosure'],
			'addEnclosure'         => $lizenz['addEnclosure'],
			'published'            => $lizenz['published']
		);
		\Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung_items %s')
		                        ->set($set)
		                        ->execute();
	}
}


// Mails importieren
$objMails = Contao\Database::getInstance()->query('SELECT * FROM tl_trainerlizenzen_mails');
if($objMails->numRows)
{
	while($objMails->next())
	{
		$set = array
		(
			'id'                   => $objMails->id,
			'pid'                  => $objMails->pid,
			'tstamp'               => $objMails->tstamp,
			'template'             => $objMails->template,
			'subject'              => $objMails->subject,
			'content'              => $objMails->content,
			'insertLizenz'         => $objMails->insertLizenz,
			'insertLizenzCard'     => 0,
			'copyVerband'          => $objMails->copyVerband,
			'copyDSB'              => $objMails->copyDSB,
			'sent_state'           => $objMails->sent_state,
			'sent_date'            => $objMails->sent_date,
			'sent_text'            => $objMails->sent_text
		);
		$objRecord = \Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung_mails %s')
		                                     ->set($set)
		                                     ->execute();
	}
}

// Referenten importieren
$objReferent = Contao\Database::getInstance()->query('SELECT * FROM tl_trainerlizenzen_referenten');
if($objReferent->numRows)
{
	while($objReferent->next())
	{
		$set = array
		(
			'id'                   => $objReferent->id,
			'tstamp'               => $objReferent->tstamp,
			'verband'              => $objReferent->verband,
			'funktion'             => $objReferent->funktion,
			'nachname'             => $objReferent->nachname,
			'vorname'              => $objReferent->vorname,
			'titel'                => $objReferent->titel,
			'plz'                  => $objReferent->plz,
			'ort'                  => $objReferent->ort,
			'strasse'              => $objReferent->strasse,
			'telefon1'             => $objReferent->telefon1,
			'telefon2'             => $objReferent->telefon2,
			'telefax1'             => $objReferent->telefax1,
			'telefax2'             => $objReferent->telefax2,
			'email'                => $objReferent->email,
			'info'                 => $objReferent->info,
			'published'            => $objReferent->published,
			'sent_info'            => $objReferent->sent_info,
			'sent_date'            => $objReferent->sent_date
		);
		$objRecord = \Database::getInstance()->prepare('INSERT INTO tl_lizenzverwaltung_referenten %s')
		                                     ->set($set)
		                                     ->execute();
	}
}
