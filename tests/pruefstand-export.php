<?php

/**
 * Ergaenzender Pruefstand: erzeugt die Excel-Datei des Lizenzexports wirklich.
 *
 * Aufruf aus dem Wurzelverzeichnis einer Contao-Installation:
 *   php <pfad>/pruef_export.php > lizenzen.xls
 *
 * Die Diagnose geht nach STDERR, allein die Excel-Datei nach STDOUT.
 * exportTrainer_XLS() beendet den Prozess, deshalb haengt das Aufraeumen der
 * Pruefdaten an register_shutdown_function().
 */

use Contao\Controller;
use Contao\Database;
use Contao\System;

$root = getcwd();

$stufe = error_reporting();
error_reporting($stufe & ~E_DEPRECATED);
require $root.'/vendor/autoload.php';
error_reporting($stufe);

$bundleDir = str_replace('\\', '/', realpath($root.'/vendor/schachbulle/contao-lizenzverwaltung-bundle'));
$befunde   = array();

set_error_handler(static function ($no, $str, $file, $line) use ($bundleDir, &$befunde) {
	if (str_starts_with(str_replace('\\', '/', (string) $file), $bundleDir)) {
		$befunde[] = sprintf('[%d] %s in %s:%d', $no, $str, substr(str_replace('\\', '/', (string) $file), \strlen($bundleDir) + 1), $line);
	}

	return true;
});

$kernel = Contao\ManagerBundle\HttpKernel\ContaoKernel::fromInput($root, new Symfony\Component\Console\Input\ArrayInput(array('--env' => 'prod')));
$kernel->boot();

$container = $kernel->getContainer();
System::setContainer($container);

$request = Symfony\Component\HttpFoundation\Request::create('/contao?do=lizenzverwaltung&key=exportXLS');
$request->attributes->set('_scope', 'backend');
$session = new Symfony\Component\HttpFoundation\Session\Session(new Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
$beBag   = new Contao\CoreBundle\Session\Attribute\ArrayAttributeBag('_contao_be_attributes');
$beBag->setName('contao_backend');
$session->registerBag($beBag);
$request->setSession($session);
$container->get('request_stack')->push($request);
$container->get('contao.framework')->initialize();
$_SESSION = array();

Contao\Input::setGet('key', 'exportXLS');

$db = Database::getInstance();
$db->prepare("DELETE FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen = ?")->execute('3');
$db->prepare("INSERT INTO tl_lizenzverwaltung_verbaende (tstamp, name, kennzeichen, published) VALUES (?,?,?,?)")->execute(time(), 'Berlin', '3', '1');
$db->prepare("DELETE FROM tl_lizenzverwaltung WHERE alias = ?")->execute('exporttest');
$db->prepare("INSERT INTO tl_lizenzverwaltung (tstamp, alias, vorname, name, geburtstag, geschlecht, email, published) VALUES (?,?,?,?,?,?,?,?)")
   ->execute(time(), 'exporttest', 'Bernd', 'Beispiel', (string) mktime(0, 0, 0, 3, 4, 1975), 'm', 'b@example.org', '1');
$pid = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung WHERE alias = ?")->limit(1)->execute('exporttest')->id;
$db->prepare("INSERT INTO tl_lizenzverwaltung_items (pid, tstamp, verband, lizenz, erwerb, gueltigkeit, published) VALUES (?,?,?,?,?,?,?)")
   ->execute($pid, time(), '3', 'B', (string) mktime(0, 0, 0, 1, 1, 2023), (string) mktime(0, 0, 0, 12, 31, 2029), '1');

register_shutdown_function(static function () use ($db, $pid, &$befunde) {
	$db->prepare("DELETE FROM tl_lizenzverwaltung_items WHERE pid = ?")->execute($pid);
	$db->prepare("DELETE FROM tl_lizenzverwaltung WHERE id = ?")->execute($pid);
	$db->prepare("DELETE FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen = ?")->execute('3');

	if ($befunde) {
		fwrite(STDERR, "MELDUNGEN aus dem Bundle:\n");

		foreach (array_unique($befunde) as $b) {
			fwrite(STDERR, "  - $b\n");
		}
	} else {
		fwrite(STDERR, "Keine Meldungen aus dem Bundle.\n");
	}
});

// Suche, Standardfilter und Spezialfilter setzen, damit alle Zweige laufen
$beBag->set('search', array('tl_lizenzverwaltung' => array('field' => 'name', 'value' => 'Beispiel')));
$beBag->set('filter', array('tl_lizenzverwaltung' => array('limit' => '0,30', 'geschlecht' => 'm'), 'tl_lizenzverwaltungFilter' => array('tli_filter' => 'V3')));

Controller::loadDataContainer('tl_lizenzverwaltung');

$dc = new Contao\DC_Table('tl_lizenzverwaltung');
$e  = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzExport();
$r  = $e->getRecords($dc);

fwrite(STDERR, 'Datensätze mit Suche+Filter+Spezialfilter: '.\count($r)."\n");

if (1 !== \count($r) || 'Beispiel' !== $r[0]['name'] || 'Berlin' !== $r[0]['verband']) {
	fwrite(STDERR, 'FEHLER: unerwartetes Ergebnis: '.json_encode($r)."\n");
}

// Gegenprobe: ein Suchbegriff, der nicht passt, muss die Menge leeren
$beBag->set('search', array('tl_lizenzverwaltung' => array('field' => 'name', 'value' => 'GibtEsNicht')));

if (array() !== $e->getRecords($dc)) {
	fwrite(STDERR, "FEHLER: Suche filtert nicht\n");
} else {
	fwrite(STDERR, "Gegenprobe Suche: leer, richtig\n");
}

// Gegenprobe: ein erfundener Feldname darf nicht in die Abfrage gelangen
$beBag->set('search', array('tl_lizenzverwaltung' => array('field' => 'name = 1 OR 1', 'value' => 'x')));

if (1 !== \count($e->getRecords($dc))) {
	fwrite(STDERR, "FEHLER: unbekanntes Suchfeld wurde nicht verworfen\n");
} else {
	fwrite(STDERR, "Gegenprobe Suchfeld: verworfen, richtig\n");
}

$beBag->set('search', array('tl_lizenzverwaltung' => array('field' => 'name', 'value' => 'Beispiel')));

// Ab hier geht allein die Excel-Datei nach STDOUT
$e->exportTrainer_XLS($dc);
