<?php

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Class dsb_trainerlizenzExport
  */
class TrainerlizenzExport extends \Backend
{

	/**
	 * Funktion exportTrainer_XLS
	 * @param object
	 * @return string
	 */

	public function exportTrainer_XLS(\DataContainer $dc)
	{
		if ($this->Input->get('key') != 'exportXLS')
		{
			return '';
		}

		$arrExport = self::getRecords($dc); // Lizenzen auslesen

		// Neues Excel-Objekt erstellen
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

		// Dokument-Eigenschaften setzen
		$spreadsheet->getProperties()->setCreator('ContaoLizenzverwaltungBundle')
		            ->setLastModifiedBy('ContaoLizenzverwaltungBundle')
		            ->setTitle('Lizenzen Deutscher Schachbund')
		            ->setSubject('Lizenzen Deutscher Schachbund')
		            ->setDescription('Export der Lizenzen im Deutschen Schachbund')
		            ->setKeywords('export lizenzen dsb schachbund')
		            ->setCategory('Export Lizenzen DSB');

		// Tabellenblätter definieren
		$sheets = array('Lizenzen');
		$styleArray = [
		    'font' => [
		        'bold' => true,
		    ],
		    'alignment' => [
		        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
		    ],
		    'borders' => [
		        'bottom' => [
		            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		        ],
		    ],
		    'fill' => [
		        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
		        'rotation' => 90,
		        'startColor' => [
		            'argb' => 'FFA0A0A0',
		        ],
		        'endColor' => [
		            'argb' => 'FFFFFFFF',
		        ],
		    ],
		];
		$styleArray2 = [
		    'alignment' => [
		        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
		    ],
		];

		// Preise-Tabelle anlegen und füllen
		$spreadsheet->createSheet();
		$spreadsheet->setActiveSheetIndex(0);
		foreach(range('A','Y') as $columnID)
		{
			$spreadsheet->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}
		$spreadsheet->getActiveSheet()->getStyle('A1:X1')->applyFromArray($styleArray);
		$spreadsheet->getActiveSheet()->getStyle('A2:X1000')->applyFromArray($styleArray2);
		$spreadsheet->getActiveSheet()->setTitle('Lizenzen')
		            ->setCellValue('A1', 'Name')
		            ->setCellValue('B1', 'Vorname')
		            ->setCellValue('C1', 'Titel')
		            ->setCellValue('D1', 'Geburtsdatum')
		            ->setCellValue('E1', 'Geschlecht')
		            ->setCellValue('F1', 'PLZ')
		            ->setCellValue('G1', 'Ort')
		            ->setCellValue('H1', 'Straße')
		            ->setCellValue('I1', 'E-Mail')
		            ->setCellValue('J1', 'Verband')
		            ->setCellValue('K1', 'DOSB-Lizenz')
		            ->setCellValue('L1', 'DSB-Lizenz')
		            ->setCellValue('M1', 'Lizenz-Art')
		            ->setCellValue('N1', 'Gültig bis')
		            ->setCellValue('O1', 'Lizenz-Erwerb')
		            ->setCellValue('P1', 'Letzte Verlängerung')
		            ->setCellValue('Q1', 'Codex')
		            ->setCellValue('R1', 'Codex-Datum')
		            ->setCellValue('S1', 'Erste Hilfe')
		            ->setCellValue('T1', 'Erste-Hilfe-Datum')
		            ->setCellValue('U1', 'Letzte Änderung')
		            ->setCellValue('V1', 'Bemerkung')
		            ->setCellValue('W1', 'Veröffentlicht')
		            ->setCellValue('X1', 'Zeitstempel');

		// Daten schreiben
		$zeile = 2;
		foreach($arrExport as $item)
		{
			$spreadsheet->getActiveSheet()
			            ->setCellValue('A'.$zeile, $item['name'])
			            ->setCellValue('B'.$zeile, $item['vorname'])
			            ->setCellValue('C'.$zeile, $item['titel'])
			            ->setCellValue('D'.$zeile, $item['geburtstag'])
			            ->setCellValue('E'.$zeile, $item['geschlecht'])
			            ->setCellValue('F'.$zeile, $item['plz'])
			            ->setCellValue('G'.$zeile, $item['ort'])
			            ->setCellValue('H'.$zeile, $item['strasse'])
			            ->setCellValue('I'.$zeile, $item['email'])
			            ->setCellValue('J'.$zeile, $item['verband'])
			            ->setCellValue('K'.$zeile, $item['lizenznummer_dosb'])
			            ->setCellValue('L'.$zeile, $item['lizenznummer'])
			            ->setCellValue('M'.$zeile, $item['lizenz'])
			            ->setCellValue('N'.$zeile, $item['gueltigkeit'])
			            ->setCellValue('O'.$zeile, $item['erwerb'])
			            ->setCellValue('P'.$zeile, $item['verlaengerungen'])
			            ->setCellValue('Q'.$zeile, $item['codex'])
			            ->setCellValue('R'.$zeile, $item['codex_date'])
			            ->setCellValue('S'.$zeile, $item['help'])
			            ->setCellValue('T'.$zeile, $item['help_date'])
			            ->setCellValue('U'.$zeile, $item['letzteAenderung'])
			            ->setCellValue('V'.$zeile, $item['bemerkung'])
			            ->setCellValue('W'.$zeile, $item['published'])
			            ->setCellValue('X'.$zeile, $item['tstamp']);
			$zeile++;
		}

		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->freezePane('A1'); // Keine Ahnung, ob damit die Zelle aktiviert wird, um eine Markierung aufzuheben

		// Überflüssiges Tabellenblatt 'Worksheet 1' löschen
		$sheetIndex = $spreadsheet->getIndex(
		    $spreadsheet->getSheetByName('Worksheet 1')
		);
		$spreadsheet->removeSheetByIndex($sheetIndex);		

		$dateiname = 'Lizenzen_'.date('Ymd-Hi').'.xls';

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
		//$writer->save('bundles/contaolizenzverwaltung/'.$dateiname); // Auf Server speichern

		// Redirect output to a client’s web browser (Xls)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$dateiname.'"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
		$writer->save('php://output'); // An Browser schicken

	}

	public function getRecords(\DataContainer $dc)
	{
		// Liest die Datensätze der Lizenzverwaltung in ein Array

		// Suchbegriff in aktueller Ansicht laden
		$search = $dc->Session->get('search');
		$search = $search[$dc->table]; // Das Array enthält field und value
		//if($search['field']) $sql = " WHERE ".$search['field']." LIKE '%%".$search['value']."%%'"; // findet auch Umlaute, Suche nach "ba" findet auch "bä"
		if($search['field'] && $search['value']) $sql = " WHERE LOWER(CAST(".$search['field']." AS CHAR)) REGEXP LOWER('".$search['value']."')"; // Contao-Standard, ohne Umlaute, Suche nach "ba" findet nicht "bä"
		else $sql = '';

		// Filter in aktueller Ansicht laden. Beispiel mit Spezialfilter (tli_filter):
		//
		// [filter] => Array
		//       (
		//           [tl_lizenzverwaltungFilter] => Array
		//               (
		//                   [tli_filter] => V2
		//               )
		// 
		//           [tl_lizenzverwaltung] => Array
		//               (
		//                   [limit] => 0,30
		//                   [geschlecht] => w
		//               )
		// 
		//       )
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

		// Spezialfilter berücksichtigen
		$filter = $dc->Session->get('filter');
		$filter = $filter[$dc->table.'Filter']['tli_filter']; // Wert aus Spezialfilter
		switch($filter)
		{
			case '1': // Alle Personen mit gültigen Lizenzen
				($sql) ? $sql .= ' AND' : $sql = 'WHERE';
				$sql .= " tl_lizenzverwaltung_items.gueltigkeit >= ".time();
				break;

			case '2': // Alle Personen mit ungültigen Lizenzen
				($sql) ? $sql .= ' AND' : $sql = 'WHERE';
				$sql .= " tl_lizenzverwaltung_items.gueltigkeit < ".time();
				break;

			case '3': // Alle Personen mit markierten Lizenzen
				($sql) ? $sql .= ' AND' : $sql = 'WHERE';
				$sql .= " tl_lizenzverwaltung_items.marker = 1";
				break;

			case 'VS': // Lizenzen Deutscher Schachbund
			case 'V1': // Lizenzen Baden
			case 'V2': // Lizenzen Bayern
			case 'V3': // Lizenzen Berlin
			case 'VD': // Lizenzen Brandenburg
			case 'VB': // Lizenzen Bremen
			case 'V4': // Lizenzen Hamburg
			case 'V5': // Lizenzen Hessen
			case 'VE': // Lizenzen Mecklenburg-Vorpommern
			case 'V7': // Lizenzen Niedersachsen
			case 'V6': // Lizenzen Nordrhein-Westfalen
			case 'V8': // Lizenzen Rheinland-Pfalz
			case 'V9': // Lizenzen Saarland
			case 'VF': // Lizenzen Sachsen
			case 'VH': // Lizenzen Sachsen-Anhalt
			case 'VA': // Lizenzen Schleswig-Holstein
			case 'VG': // Lizenzen Thüringen
			case 'VC': // Lizenzen Württemberg'
				($sql) ? $sql .= ' AND' : $sql = 'WHERE';
				$sql .= " tl_lizenzverwaltung_items.verband = '".substr($filter,1,1)."'";
				break;

			default:
		}

		($sql) ? $sql .= " AND tl_lizenzverwaltung_items.published = '1' ORDER BY name,vorname ASC" : $sql = " WHERE tl_lizenzverwaltung_items.published = '1' ORDER BY name,vorname ASC";

		$sql = "SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id".$sql;
		
		log_message('Excel-Export mit: '.$sql, 'lizenzverwaltung.log');
		// Datensätze laden
		$records = \Database::getInstance()->prepare($sql)
		                                   ->execute();

		$verbandsname = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbaende(); // Verbandskurzzeichen und -namen laden

		// Datensätze umwandeln
		$arrExport = array();
		if($records->numRows)
		{
			while($records->next()) 
			{
				$arrExport[] = array
				(
					'vorname'           => $records->vorname,
					'name'              => $records->name,
					'lizenznummer_dosb' => $records->license_number_dosb,
					'lizenznummer'      => $records->lizenznummer,
					'lizenz'            => $records->lizenz,
					'gueltigkeit'       => $this->getDate($records->gueltigkeit),
					'geburtstag'        => $this->getDate($records->geburtstag),
					'geschlecht'        => $records->geschlecht,
					'strasse'           => $records->strasse,
					'plz'               => $records->plz,
					'ort'               => $records->ort,
					'erwerb'            => $this->getDate($records->erwerb),
					'verlaengerungen'   => $this->getDate(\Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerlaengerung($records->erwerb, $records->verlaengerungen)),
					'codex'             => $records->codex,
					'codex_date'        => $this->getDate($records->codex_date),
					'help'              => $records->help,
					'help_date'         => $this->getDate($records->help_date),
					'letzteAenderung'   => $this->getDate($records->letzteAenderung),
					'bemerkung'         => strip_tags($records->bemerkung),
					'published'         => $records->published,
					'titel'             => $records->titel,
					'email'             => $records->email,
					'verband'           => $verbandsname[$records->verband],
					'tstamp'            => date("d.m.Y H:i:s",$records->tstamp)
				);
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