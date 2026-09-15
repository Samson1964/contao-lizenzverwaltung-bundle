<?php

/**
 * Prueft, dass die LiMS-Routen ohne angemeldeten Backend-Benutzer nichts tun.
 *
 * Aufruf aus dem Wurzelverzeichnis einer Contao-Installation.
 */

use Contao\Database;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

$root = getcwd();

$stufe = error_reporting();
error_reporting($stufe & ~E_DEPRECATED);
require $root.'/vendor/autoload.php';
error_reporting($stufe);

$kernel = Contao\ManagerBundle\HttpKernel\ContaoKernel::fromInput($root, new Symfony\Component\Console\Input\ArrayInput(array('--env' => 'prod')));
$kernel->boot();

System::setContainer($kernel->getContainer());
$kernel->getContainer()->get('contao.framework')->initialize();

$db = Database::getInstance();
$db->prepare("DELETE FROM tl_lizenzverwaltung WHERE alias = ?")->execute('routetest');
$db->prepare("INSERT INTO tl_lizenzverwaltung (tstamp, alias, vorname, name, published) VALUES (?,?,?,?,?)")->execute(time(), 'routetest', 'Rita', 'Route', '1');
$pid = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung WHERE alias = ?")->limit(1)->execute('routetest')->id;
$db->prepare("INSERT INTO tl_lizenzverwaltung_items (pid, tstamp, verband, lizenz, published, dosb_tstamp, dosb_code) VALUES (?,?,?,?,?,?,?)")
   ->execute($pid, time(), '3', 'C', '1', 0, 0);
$id = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung_items WHERE pid = ?")->limit(1)->execute($pid)->id;

$vorher = $db->prepare("SELECT dosb_tstamp, dosb_code FROM tl_lizenzverwaltung_items WHERE id = ?")->execute($id)->row();

foreach (array('export', 'umzug') as $aktion) {
	$request  = Request::create('/_lizenzverwaltung/lims/'.$aktion.'/'.$id);
	$response = $kernel->handle($request);

	echo str_pad($aktion, 8).' → HTTP '.$response->getStatusCode();
	echo ' ('.substr(strip_tags((string) $response->getContent()), 0, 60).")\n";

	if (200 === $response->getStatusCode()) {
		echo "  FEHLER: Route ist ohne Anmeldung erreichbar!\n";
	}

	$kernel->terminate($request, $response);
}

$nachher = $db->prepare("SELECT dosb_tstamp, dosb_code FROM tl_lizenzverwaltung_items WHERE id = ?")->execute($id)->row();

if ($vorher !== $nachher) {
	echo "FEHLER: Der Datensatz wurde trotzdem verändert: ".json_encode($nachher)."\n";
} else {
	echo "Datensatz unverändert — richtig.\n";
}

$db->prepare("DELETE FROM tl_lizenzverwaltung_items WHERE pid = ?")->execute($pid);
$db->prepare("DELETE FROM tl_lizenzverwaltung WHERE id = ?")->execute($pid);
