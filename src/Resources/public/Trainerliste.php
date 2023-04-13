<?php
ini_set('display_errors', '1');

/**
 * Contao Open Source CMS, Copyright (C) 2005-2016 Leo Feyer
 *
 * Trainerliste
 * ============
 * Verschickt an alle Referenten einer Liste der Trainer im Excelformat
 *
 * @copyright	Frank Hoppe 2016 <webmaster@schachbund.de>
 * @author      Frank Hoppe
 * @package     Banner
 * @license     LGPL
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
define('TL_SCRIPT', 'bundles/contaolizenzverwaltung/Trainerliste.php');

require($_SERVER['DOCUMENT_ROOT'].'/../system/initialize.php');

/**
 * Class Trainerliste
 *
 * @copyright  Frank Hoppe 2016
 * @author     Frank Hoppe
 * @package    trainerlizenzen
 */
class Trainerliste
{
	public function run()
	{

		$Verbandsname = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbaende();

		// Referenten laden, die informiert werden wollen
		$objReferenten = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE sent_info = ? AND published = ? AND email != ?")
		                                         ->execute(1, 1, '');
		// DSB-Verantwortliche laden, die informiert werden wollen
		$objDSBReferenten = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_referenten WHERE sent_info = ? AND verband = ? AND published = ? AND email != ?")
		                                            ->execute(1, 'S', 1, '');

		if($objReferenten->numRows)
		{
			echo $objReferenten->numRows." Referenten gefunden<br>\n";
			while($objReferenten->next())
			{
				// Quartal des letzten Versandes ermitteln
				$QuartalLast = floor(date("m", $objReferenten->sent_date) / 3);
				$QuartalNow = floor(date("m") / 3);
				//$QuartalLast = 1;
				//$QuartalNow = 2;

				echo ".. Letztes Quartal: $QuartalLast | Aktuelles Quartal: $QuartalNow<br>\n";
				if($QuartalLast != $QuartalNow || $objReferenten->sent_date == 0)
				{
					echo ".... Lizenzen von Verband ".$objReferenten->verband." werden geladen<br>\n";
					// Lizenzen des Verbandes laden
					$objLizenzen = \Database::getInstance()->prepare("SELECT * FROM tl_lizenzverwaltung_items LEFT JOIN tl_lizenzverwaltung ON tl_lizenzverwaltung_items.pid = tl_lizenzverwaltung.id WHERE tl_lizenzverwaltung_items.verband = ? AND tl_lizenzverwaltung_items.published = ? AND tl_lizenzverwaltung.published = ? ORDER BY tl_lizenzverwaltung.name,tl_lizenzverwaltung.vorname ASC")
					                                       ->execute($objReferenten->verband, 1, 1);

					if($objLizenzen->numRows)
					{
						echo "...... ".$objLizenzen->numRows." Lizenzen gefunden<br>";
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

						// Lizenzen-Tabelle anlegen und füllen
						$spreadsheet->createSheet();
						$spreadsheet->setActiveSheetIndex(0);
						foreach(range('A','X') as $columnID)
						{
							$spreadsheet->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
						}
						$spreadsheet->getActiveSheet()->getStyle('A1:X1')->applyFromArray($styleArray);
						$spreadsheet->getActiveSheet()->getStyle('A2:X1000')->applyFromArray($styleArray2);
						$spreadsheet->getActiveSheet()->setTitle('Lizenzen')
						            ->setCellValue('A1', 'Nachname')
						            ->setCellValue('B1', 'Vorname')
						            ->setCellValue('C1', 'Titel')
						            ->setCellValue('D1', 'Geburtsdatum')
						            ->setCellValue('E1', 'Geschlecht')
						            ->setCellValue('F1', 'PLZ')
						            ->setCellValue('G1', 'Ort')
						            ->setCellValue('H1', 'Straße')
						            ->setCellValue('I1', 'Telefon')
						            ->setCellValue('J1', 'E-Mail')
						            ->setCellValue('K1', 'Verband')
						            ->setCellValue('L1', 'DOSB-Lizenz')
						            ->setCellValue('M1', 'DSB-Lizenz')
						            ->setCellValue('N1', 'Lizenz-Art')
						            ->setCellValue('O1', 'Gültig bis')
						            ->setCellValue('P1', 'Lizenz-Erwerb')
						            ->setCellValue('Q1', 'Letzte Verlängerung')
						            ->setCellValue('R1', 'Codex')
						            ->setCellValue('S1', 'Codex-Datum')
						            ->setCellValue('T1', 'Erste Hilfe')
						            ->setCellValue('U1', 'Erste-Hilfe-Datum')
						            ->setCellValue('V1', 'Letzte Änderung')
						            ->setCellValue('W1', 'Veröffentlicht')
						            ->setCellValue('X1', 'Zeitstempel');
						$ablaufendArr = array(); // Array für bald ablaufende Lizenzen
						$gueltig = '';
						$zeile = 2;
						while($objLizenzen->next())
						{
							$verlaengerung = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerlaengerung($objLizenzen->erwerb, $objLizenzen->verlaengerungen);
							$spreadsheet->getActiveSheet()
							            ->setCellValue('A'.$zeile, $objLizenzen->name)
							            ->setCellValue('B'.$zeile, $objLizenzen->vorname)
							            ->setCellValue('C'.$zeile, $objLizenzen->titel)
							            ->setCellValue('D'.$zeile, $this->getDate($objLizenzen->geburtstag))
							            ->setCellValue('E'.$zeile, $objLizenzen->geschlecht)
							            ->setCellValue('F'.$zeile, $objLizenzen->plz)
							            ->setCellValue('G'.$zeile, $objLizenzen->ort)
							            ->setCellValue('H'.$zeile, $objLizenzen->strasse)
							            ->setCellValue('I'.$zeile, $objLizenzen->telefon)
							            ->setCellValue('J'.$zeile, $objLizenzen->email)
							            ->setCellValue('K'.$zeile, $Verbandsname[$objLizenzen->verband])
							            ->setCellValue('L'.$zeile, $objLizenzen->license_number_dosb)
							            ->setCellValue('M'.$zeile, $objLizenzen->lizenznummer)
							            ->setCellValue('N'.$zeile, $objLizenzen->lizenz)
							            ->setCellValue('O'.$zeile, $this->getDate($objLizenzen->gueltigkeit))
							            ->setCellValue('P'.$zeile, $this->getDate($objLizenzen->erwerb))
							            ->setCellValue('Q'.$zeile, $this->getDate($verlaengerung))
							            ->setCellValue('R'.$zeile, $objLizenzen->codex)
							            ->setCellValue('S'.$zeile, $this->getDate($objLizenzen->codex_date))
							            ->setCellValue('T'.$zeile, $objLizenzen->help)
							            ->setCellValue('U'.$zeile, $this->getDate($objLizenzen->help_date))
							            ->setCellValue('V'.$zeile, $this->getDate($objLizenzen->letzteAenderung))
							            ->setCellValue('W'.$zeile, $objLizenzen->published)
							            ->setCellValue('X'.$zeile, date("d.m.Y H:i:s",$objLizenzen->tstamp));
							$zeile++;
							// Läuft die Lizenz bald ab?
							if($objLizenzen->gueltigkeit > time() && strtotime("+6 month") > $objLizenzen->gueltigkeit)
							{
								// Gültigkeit größer aktuelle Zeit und aktuelle Zeit + 6 Monate größer als Gültigkeit
								$ablaufendArr[] = array
								(
									'gueltigkeit'      => $objLizenzen->gueltigkeit,
									'name'             => $objLizenzen->name,
									'vorname'          => $objLizenzen->vorname,
									'lizenz'           => $objLizenzen->lizenz,
								);
							}
						}

						// Überflüssiges Tabellenblatt 'Worksheet 1' löschen
						$sheetIndex = $spreadsheet->getIndex(
						    $spreadsheet->getSheetByName('Worksheet 1')
						);
						$spreadsheet->removeSheetByIndex($sheetIndex);

						$file = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['lizenzverwaltung_versandordner']);
						$filename = 'Lizenzen_'.$objReferenten->verband.'_'.date("Ymd-Hi").".xls";
						$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
						$writer->save(TL_ROOT.'/'.$file->path.'/'.$filename); // Auf Server speichern
					}
					else
					{
						echo ".... Keine Lizenzen gefunden<br>\n";
					}
					
					$content = '<p>Hallo '.$objReferenten->vorname.' '.$objReferenten->nachname.',</p>';
					$content .= '<p>Im Anhang finden Sie die aktuelle Lizenzenliste Ihres Landesverbandes.</p>';
					// Ablaufende Lizenzen einbauen
					if($ablaufendArr)
					{
						$ablaufendArr = \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::sortArrayByFields($ablaufendArr, array('gueltigkeit' => SORT_DESC, 'name' => array(SORT_ASC, SORT_STRING), 'vorname' => array(SORT_ASC, SORT_STRING), 'lizenz' => array(SORT_ASC, SORT_STRING)));
						$content .= '<p>Folgende Lizenzen laufen innerhalb der nächsten 6 Monate ab:</p>';
						$content .= '<ul>';
						foreach($ablaufendArr as $item)
						{
							$content .= '<li>'.date('d.m.Y', $item['gueltigkeit']).' <b>'.$item['lizenz'].'</b> '.$item['name'].','.$item['vorname'].'</li>';
						}
						$content .= '</ul>';
					}
					$content .= '<p>Deutscher Schachbund<br>Lizenzverwaltung<br><a href="mailto:lizenzen@schachbund.de">lizenzen@schachbund.de</a></p>';
					$content .= '<p><i>DIESE E-MAIL WURDE AUTOMATISCH GENERIERT!</i></p>';
					// Email versenden
					$objEmail = new \Email();
					$objEmail->logFile = 'lizenzverwaltung_email.log';
					$objEmail->from = 'lizenzen@schachbund.de';
					$objEmail->fromName = 'Deutscher Schachbund';
					$objEmail->subject = '[DSB-Lizenzen] Aktuelle Liste der Lizenzen '.$Verbandsname[$objReferenten->verband];
					$objEmail->html = $content;
					$objEmail->attachFile(TL_ROOT.'/'.$file->path.'/'.$filename);
					// BCC-Empfänger festlegen
					$bcc = array();
					$bcc[] = 'Frank Hoppe <webmaster@schachbund.de>';
					$bcc[] = 'Judith Zabel <lizenzen@schachbund.de>';
					if($objDSBReferenten->numRows)
					{
						while($objDSBReferenten->next())
						{
							$bcc[] = htmlentities($objDSBReferenten->vorname.' '.$objDSBReferenten->nachname).' <'.$objDSBReferenten->email.'>';
						}
					}
					$objEmail->sendBcc($bcc);
					if($objEmail->sendTo(array($objReferenten->vorname.' '.$objReferenten->nachname.' <'.$objReferenten->email.'>')))
					{
						// Versand in Ordnung, Datum merken
						$set = array
						(
							'sent_date'  => time()
						);
						$trainer = \Database::getInstance()->prepare("UPDATE tl_lizenzverwaltung_referenten %s WHERE id = ?")
						                                   ->set($set)
						                                   ->execute($objReferenten->id);
					}
				}
			}
		}

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

/**
 * Instantiate controller
 */
$objTrainerliste = new Trainerliste();
$objTrainerliste->run();
echo 'Fertig';
