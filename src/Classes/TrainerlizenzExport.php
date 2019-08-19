<?php

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');


/**
 * Class dsb_trainerlizenzExport
  */
class TrainerlizenzExport extends \Backend
{

	/**
	 * Return a form to choose a CSV file and import it
	 * @param object
	 * @return string
	 */

	public function exportTrainer(DataContainer $dc)
	{
		if ($this->Input->get('key') != 'export')
		{
			return '';
		}

		$arrExport = $this->getRecords($dc); // Lizenzen auslesen

		$exportFile =  'DSB-Trainerlizenzen-Export' . date("Ymd-Hi");
		
		header('Content-Type: application/csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="' . $exportFile .'.csv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		$output = '';
		foreach ($arrExport as $export) 
		{
			$output .= '"' . join('";"', $export).'"' . "\n";
		}

		echo $output;
		exit;
	}

	public function exportXLSTrainer(DataContainer $dc)
	{
		if ($this->Input->get('key') != 'exportXLS')
		{
			return '';
		}

		$arrExport = $this->getRecords($dc); // Lizenzen auslesen
		
		// Excel-Export
		if (file_exists(TL_ROOT . "/system/modules/xls_export/vendor/xls_export.php")) 
		{
			include(TL_ROOT . "/system/modules/xls_export/vendor/xls_export.php");
			$xls = new \xlsexport();
			$sheet = 'Trainerlizenzen';
			$xls->addworksheet($sheet);

			// Spaltenbreite setzen
			for ($c = 1; $c <= 27; $c++)
			{
				switch($c)
				{
					case 20: // Veröffentlicht
						$breite = 4000;
						break;
					case 10: // PLZ
					case 21: // Titel
					case 24: // ID
					case 8: // Geschlecht
						$breite = 1500;
						break;
					case 5: // Lizenz
					case 14: // Codex
					case 16: // Erste Hilfe
						$breite = 2000;
						break;
					case 6: // Gültig bis
					case 7: // Geburtsdatum
					case 12: // Erwerb
						$breite = 3500;
						break;
					case 13: // Letzte Verlängerung
					case 15: // Codex Datum
					case 17: // Erste Hilfe Datum
					case 1: // Vorname
					case 2: // Name
					case 4: // Lizenznummer
					case 19: // Bemerkung
					case 18: // Letzte Änderung
						$breite = 4000;
						break;
					case 11: // Ort
					case 25: // Zeitstempel
						$breite = 4500;
						break;
					case 3: // DOSB-Lizenznummer
					case 9: // Straße
					case 22: // Emailadresse
					case 23: // Verband
						$breite = 5000;
						break;
					default:
						$breite = 4000;
				}
				$xls->setcolwidth ($sheet,$c-1,$breite);
			}
			
			// Daten schreiben
			$row = 0;
			foreach($arrExport as $data)
			{
				$col = 0;
				foreach($data as $key => $value) 
				{
					if($row == 0)
					{
						$xls->setcell(array("sheetname" => $sheet,"row" => $row, "col" => $col, 'fontweight' => XLSFONT_BOLD, 'hallign' => XLSXF_HALLIGN_CENTER, "data" => $value));
					}
					else
					{
						$xls->setcell(array("sheetname" => $sheet,"row" => $row, "col" => $col, 'hallign' => XLSXF_HALLIGN_LEFT, "data" => $value));
					}
					$col++;
				}
				$row++; // Zeilenzähler modifizieren
			}
			$xls->sendFile($sheet . '_' . date("Ymd-Hi") . ".xls");
		} 
		else 
		{
			echo "<html><head><title>Need extension xls_export</title></head><body>"
			    ."Please install the extension 'xls_export' 3.x.<br /><br />"
			    ."Bitte die Erweiterung 'xls_export' 3.x installieren.<br /><br />"
			    ."Installer l'extension 'xls_export' 3.x s'il vous plaît."
			    ."</body></html>";
		}
	}

	public function getRecords(DataContainer $dc)
	{
		// Liest die Datensätze der Trainerlizenzen in ein Array

		// Suchbegriff in aktueller Ansicht laden
		$search = $dc->Session->get('search');
		$search = $search[$dc->table]; // Das Array enthält field und value
		//if($search['field']) $sql = " WHERE ".$search['field']." LIKE '%%".$search['value']."%%'"; // findet auch Umlaute, Suche nach "ba" findet auch "bä"
		if($search['field'] && $search['value']) $sql = " WHERE LOWER(CAST(".$search['field']." AS CHAR)) REGEXP LOWER('".$search['value']."')"; // Contao-Standard, ohne Umlaute, Suche nach "ba" findet nicht "bä"
		else $sql = '';

		// Filter in aktueller Ansicht laden
		$filter = $dc->Session->get('filter');
		$filter = $filter[$dc->table]; // Das Array enthält limit (Wert meistens = 0,30) und alle Feldnamen mit den Werten
		foreach($filter as $key => $value)
		{
			if($key != 'limit')
			{
				($sql) ? $sql .= ' AND' : $sql = ' WHERE';
				$sql .= " ".$key." = '".$value."'";
			}
		}
		($sql) ? $sql .= " AND published = '1' ORDER BY name,vorname ASC" : $sql = " WHERE published = '1' ORDER BY name,vorname ASC";

		//echo "|$sql|";
		//exit;
		log_message('Excel-Export mit: SELECT * FROM tl_trainerlizenzen'.$sql, 'trainerlizenzen.log');
		// Datensätze laden
		$records = \Database::getInstance()->prepare('SELECT * FROM tl_trainerlizenzen'.$sql)
										   ->execute();

		$verbandsname = \Samson\Trainerlizenzen\Helper::getVerbaende(); // Verbandskurzzeichen und -namen laden

		// Datensätze umwandeln
		$arrExport = array();
		// Kopfzeile anlegen
		$arrExport[0]['vorname'] = 'Vorname';
		$arrExport[0]['name'] = 'Name';
		$arrExport[0]['lizenznummer_dosb'] = 'DOSB-Lizenz';
		$arrExport[0]['lizenznummer'] = 'Lizenznummer';
		$arrExport[0]['lizenz'] = 'Lizenz';
		$arrExport[0]['gueltigkeit'] = utf8_decode('Gültig bis');
		$arrExport[0]['geburtstag'] = 'Geburtsdatum';
		$arrExport[0]['geschlecht'] = 'Geschlecht';
		$arrExport[0]['strasse'] = 'Strasse';
		$arrExport[0]['plz'] = 'PLZ';
		$arrExport[0]['ort'] = 'Ort';
		$arrExport[0]['erwerb'] = 'Lizenzerwerb';
		$arrExport[0]['verlaengerungen'] = utf8_decode('Letzte Verlängerung');
		$arrExport[0]['codex'] = 'Codex';
		$arrExport[0]['codex_date'] = 'Codex-Datum';
		$arrExport[0]['help'] = 'Erste Hilfe';
		$arrExport[0]['help_date'] = 'Erste-Hilfe-Datum';
		$arrExport[0]['letzteAenderung'] = utf8_decode('Letzte Änderung');
		$arrExport[0]['bemerkung'] = 'Bemerkung';
		$arrExport[0]['published'] = utf8_decode('Veröffentlicht');
		$arrExport[0]['titel'] = 'Titel';
		$arrExport[0]['email'] = 'Emailadresse';
		$arrExport[0]['verband'] = 'Verband';
		$arrExport[0]['id'] = 'ID';
		$arrExport[0]['tstamp'] = 'Zeitstempel';
		$x = 1;
		if($records->numRows)
		{
			while($records->next()) 
			{
				$arrExport[$x]['vorname'] = utf8_decode($records->vorname);
				$arrExport[$x]['name'] = utf8_decode($records->name);
				$arrExport[$x]['lizenznummer_dosb'] = utf8_decode($records->license_number_dosb);
				$arrExport[$x]['lizenznummer'] = utf8_decode($records->lizenznummer);
				$arrExport[$x]['lizenz'] = $records->lizenz;
				$arrExport[$x]['gueltigkeit'] = $this->getDate($records->gueltigkeit);
				$arrExport[$x]['geburtstag'] = $this->getDate($records->geburtstag);
				$arrExport[$x]['geschlecht'] = $records->geschlecht;
				$arrExport[$x]['strasse'] = utf8_decode($records->strasse);
				$arrExport[$x]['plz'] = $records->plz;
				$arrExport[$x]['ort'] = utf8_decode($records->ort);
				$arrExport[$x]['erwerb'] = $this->getDate($records->erwerb);
				$arrExport[$x]['verlaengerungen'] = $this->getDate(\Samson\Trainerlizenzen\Helper::getVerlaengerung($records->erwerb, $records->verlaengerungen));
				$arrExport[$x]['codex'] = $records->codex;
				$arrExport[$x]['codex_date'] = $this->getDate($records->codex_date);
				$arrExport[$x]['help'] = $records->help;
				$arrExport[$x]['help_date'] = $this->getDate($records->help_date);
				$arrExport[$x]['letzteAenderung'] = $this->getDate($records->letzteAenderung);
				$arrExport[$x]['bemerkung'] = strip_tags(utf8_decode($records->bemerkung));
				$arrExport[$x]['published'] = $records->published;
				$arrExport[$x]['titel'] = $records->titel;
				$arrExport[$x]['email'] = $records->email;
				$arrExport[$x]['verband'] = utf8_decode($verbandsname[$records->verband]);
				$arrExport[$x]['id'] = $records->id;
				$arrExport[$x]['tstamp'] = date("d.m.Y H:i:s",$records->tstamp);
				$x++;
			}
		}
		return $arrExport;
	}

	/**
	 * Datumswert aus Datenbank umwandeln
	 * @param mixed
	 * @return mixed
	 */
	public function getDate($varValue)
	{
		return trim($varValue) ? date('d.m.Y', $varValue) : '';
	}

}
?>