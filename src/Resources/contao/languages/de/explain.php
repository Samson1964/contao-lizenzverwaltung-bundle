<?php

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_plz'] = array
(
	array('colspan', 'Der DOSB akzeptiert nur 5-stellige Postleitzahlen. Ist die Postleitzahl kürzer, sind Nullen (0) voranzustellen. Länderkennzeichnungen bitte beim Ort eintragen!'),
);

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_strasse'] = array
(
	array('colspan', '<b>Muss ich etwas besonders berücksichtigen, wenn die Person im Ausland wohnt?</b>'),
	array('colspan', 'Im DOSB-Lizenzmanagementsystem gibt es keine Spalte „Land“. D. h. bei Personen, die im Ausland wohnen, muss Folgendes berücksichtigt werden:'),
	array('1.)', 'alle Zusatztexte der Adresse sollten in die Spalte „Straße und Hausnummer“ eingetragen werden'), 
	array('2.)', 'die PLZ muss 5-stellig sein, d.h. bei bspw. 3-stelligen PLZ bitte zwei 00 voranstellen'), 
	array('3.)', 'das Land sollte bitte in der Spalte „Ort“ hinter die Ortsangabe dazu eingetragen werden.'), 
	array('colspan', '<i>Quelle: <a href="bundles/contaolizenzverwaltung/pdf/Leitfaden_LiMS_09.04.2019.pdf" target="_blank">DOSB-Leitfaden LiMS 09.04.2019</a>, S. 21</i>'),
);

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_erwerb'] = array
(
	array('colspan', '<b>Was mache ich, wenn mir das Datum der Erstausstellung einer Lizenz nicht bekannt ist?</b>'),
	array('colspan', 'Ist das Datum der Erstausstellung nicht bekannt, gebe bitte 01.01.1900 an. Es handelt sich hierbei um einen extra festgelegten System-Code. In diesem Falle wird auf der Lizenz als Erstausstellungsdatum „vor 2016“ abgedruckt!'),
	array('colspan', '<i>Quelle: <a href="bundles/contaolizenzverwaltung/pdf/Leitfaden_LiMS_09.04.2019.pdf" target="_blank">DOSB-Leitfaden LiMS 09.04.2019</a>, S. 21</i>'),
);

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_codex'] = array
(
	array('colspan', '<b>Was muss ich bei Ehrenkodex und Erste-Hilfe-Ausbildung eintragen?</b>'),
	array('colspan', 'Die hier zu treffenden Angaben sind „Ja“ (für liegt vor) oder „Nein“ (für liegt nicht vor). Wenn Sie „Ja“ angeben, wird der Nachweis auch auf der 2. Seite der Lizenz erwähnt. Wenn Sie unsicher sind, ob die Unterlagen vorliegen, wählen Sie „Nein“. Die Angabe des Datums der Vorlage ist optional.'),
	array('colspan', '<i>Quelle: <a href="bundles/contaolizenzverwaltung/pdf/Leitfaden_LiMS_09.04.2019.pdf" target="_blank">DOSB-Leitfaden LiMS 09.04.2019</a>, S. 21</i>'),
	array('colspan', 'Anmerkung: „Ja“ bedeutet bei uns Häkchen in der Checkbox gesetzt, „Nein“ Häkchen nicht gesetzt.'),
);

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_verlaengerung'] = array
(
	array('colspan', '<b>Verlängerung von Lizenzen</b>'),
	array('colspan', 'Der maximale Gültigkeitszeitraum für die Lizenzverlängerung beträgt vier bzw. bei A-Lizenzen zwei Jahre +92 Tage (ein Quartal).'),
	array('colspan', '<i>Quelle: <a href="bundles/contaolizenzverwaltung/pdf/Leitfaden_LiMS_09.04.2019.pdf" target="_blank">DOSB-Leitfaden LiMS 09.04.2019</a>, S. 47</i>'),
);

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_templates'] = array
(
	array('Inserttags', 'Weitere Informationen zu Inserttags finden Sie unter <a href="https://docs.contao.org/books/manual/current/de/04-inhalte-verwalten/inserttags.html" title="Contao Online-Dokumentation" target="_blank" rel="noreferrer noopener">docs.contao.org/books/manual/current/de/04-inhalte-verwalten/inserttags.html</a>.'),
	array('colspan', '<b>Verlängerung von Lizenzen</b>'),
	array('colspan', 'Der maximale Gültigkeitszeitraum für die Lizenzverlängerung beträgt vier bzw. bei A-Lizenzen zwei Jahre +92 Tage (ein Quartal).'),
	array('colspan', '<i>Quelle: <a href="bundles/contaolizenzverwaltung/pdf/Leitfaden_LiMS_09.04.2019.pdf" target="_blank">DOSB-Leitfaden LiMS 09.04.2019</a>, S. 47</i>'),
	array('colspan', '<b>Inserttags der Lizenzverwaltung</b>'),
	array('colspan', '<b style="display:inline-block; width:200px;">##css##</b> Interne Style-Sheet-Definitionen<br><b style="display:inline-block; width:200px;">##lizenz_vorname##</b> Vorname der lizenzierten Person<br><b style="display:inline-block; width:200px;">##lizenz_nachname##</b> Nachname der lizenzierten Person<br><b style="display:inline-block; width:200px;">##lizenz_geschlecht##</b> Geschlecht (m/w/d)<br><b style="display:inline-block; width:200px;">##lizenz_art##</b> Ausbildungsgang: A, B, C-Sonderlizenz usw.<br><b style="display:inline-block; width:200px;">##lizenz_nummer##</b> Lizenznummer des DOSB, z.B. DSchB-T-C-0002146<br><b style="display:inline-block; width:200px;">##lizenz_content##</b> Inhalt aus der Textarea der E-Mail<br><b style="display:inline-block; width:200px;">##lizenz_signatur##</b> Signatur aus der E-Mail bzw. aus den System-Einstellungen'),
);

$GLOBALS['TL_LANG']['XPL']['lizenzverwaltung_verbaende'] = array
(
	array('colspan', '<b>Dringend empfohlene Konfiguration für Verbände!</b>'),
	array('colspan', 'Kennzeichen = Name'),
	array('colspan', 'S = Deutscher Schachbund<br>1 = Baden<br>2 = Bayern<br>3 = Berlin<br>D = Brandenburg<br>B = Bremen<br>4 = Hamburg<br>5 = Hessen<br>E = Mecklenburg-Vorpommern<br>7 = Niedersachsen<br>6 = Nordrhein-Westfalen<br>8 = Rheinland-Pfalz<br>9 = Saarland<br>F = Sachsen<br>H = Sachsen-Anhalt<br>A = Schleswig-Holstein<br>G = Thüringen<br>C = Württemberg')
);
