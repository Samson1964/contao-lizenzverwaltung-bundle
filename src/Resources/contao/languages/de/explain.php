<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

$GLOBALS['TL_LANG']['XPL']['trainerlizenzen_plz'] = array
(
	array('colspan', 'Der DOSB akzeptiert nur 5-stellige Postleitzahlen. Ist die Postleitzahl kürzer, sind Nullen (0) voran zu stellen. Länderkennzeichnungen bitte beim Ort eintragen!'),
);

$GLOBALS['TL_LANG']['XPL']['trainerlizenzen_strasse'] = array
(
	array('colspan', '<b>Muss ich etwas besonders berücksichtigen, wenn Lizenzinhaber/innen im Ausland wohnen?</b>'),
	array('colspan', 'Im DOSB-Lizenzmanagementsystem gibt es keine Spalte „Land“. D. h. bei Lizenzinhaber/innen, die im Ausland wohnen, muss folgendes berücksichtigt werden:'),
	array('1.)', 'alle Zusatztexte der Adresse sollten in die Spalte „Straße & Nr.“ eingetragen werden'), 
	array('2.)', 'die PLZ muss 5-stellig sein, d.h. bei bspw. 3-stelligen PLZ bitte zwei 00 voranstellen'), 
	array('3.)', 'das Land sollte bitte in der Spalte „Ort“ hinter die Ortsangabe dazu eingetragen werden.'), 
	array('colspan', '<i>Quelle: <a href="system/modules/trainerlizenzen/public/Leitfaden_LiMS_11.07.2016.pdf" target="_blank">DOSB-Leitfaden LiMS 11.07.2016</a>, S. 18</i>'),
);

$GLOBALS['TL_LANG']['XPL']['trainerlizenzen_erwerb'] = array
(
	array('colspan', '<b>Was mache ich, wenn mir das Datum der Erstausstellung einer Lizenz nicht bekannt ist?</b>'),
	array('colspan', 'Ist das Datum der Erstausstellung nicht bekannt, geben Sie bitte 01.01.1900 an. Es handelt sich hierbei um einen extra festgelegten System-Code. In diesem Falle wird auf der Lizenz als Erstausstellungsdatum „vor 2016“ abgedruckt!'),
	array('colspan', '<i>Quelle: <a href="system/modules/trainerlizenzen/public/Leitfaden_LiMS_11.07.2016.pdf" target="_blank">DOSB-Leitfaden LiMS 11.07.2016</a>, S. 18</i>'),
);

$GLOBALS['TL_LANG']['XPL']['trainerlizenzen_kodex'] = array
(
	array('colspan', '<b>Was muss ich bei Ehrenkodex und Erste-Hilfe-Ausbildung eintragen?</b>'),
	array('colspan', 'Die hier zu treffenden Angaben sind „Ja“ (für liegt vor) oder „Nein“ (für liegt nicht vor). Wenn Sie „Ja“ angeben, wird der Nachweis auch auf der 2. Seite der Lizenz erwähnt. Wenn Sie unsicher sind, ob die Unterlagen vorliegen, wählen Sie „Nein“. Die Angabe des Datums der Vorlage ist optional.'),
	array('colspan', '<i>Quelle: <a href="system/modules/trainerlizenzen/public/Leitfaden_LiMS_11.07.2016.pdf" target="_blank">DOSB-Leitfaden LiMS 11.07.2016</a>, S. 18</i>'),
	array('colspan', 'Anmerkung: „Ja“ bedeutet bei uns Häkchen in der Checkbox gesetzt, „Nein“ Häkchen nicht gesetzt.'),
);
