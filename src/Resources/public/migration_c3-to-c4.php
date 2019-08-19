<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// http://development.schachbund.de/bundles/contaotrainerlizenzen/migration_c3-to-c4.php
define('TL_SCRIPT', 'MIGRATION_Trainerlizenzen');
define('TL_MODE', 'FE');

include($_SERVER['DOCUMENT_ROOT'].'/../system/initialize.php');

// Alle Lizenzen laden
$objLizenzen = Contao\Database::getInstance()->query('SELECT * FROM tl_trainerlizenzen');

$personen = array(); // Nimmt die einzelnen Personen auf, je Vorname/Nachname nur ein Datensatz!
if($objLizenzen->numRows > 0)
{
	while($objLizenzen->next())
	{
		$index = strtoupper($objLizenzen->name.'|'.$objLizenzen->vorname);
		$personen[$index][] = $objLizenzen->id;
	}
}

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
		$objRecord = \Database::getInstance()->prepare('INSERT INTO tl_trainerlizenzen_items %s')
		                                     ->set($set)
		                                     ->execute();
	}
}
