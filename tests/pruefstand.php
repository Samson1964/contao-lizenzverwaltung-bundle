<?php

/**
 * Prüfstand für schachbulle/contao-lizenzverwaltung-bundle.
 *
 * Aufruf aus dem Wurzelverzeichnis einer Contao-Installation:
 *   php <pfad>/pruefstand.php
 *
 * Fährt das Contao-Framework hoch, lädt alle DCA-Dateien des Bundles, ruft
 * jeden Rückruf einmal auf und meldet jede Warnung, Notiz oder Deprecation,
 * deren Quelle im Bundle liegt.
 */

use Contao\Controller;
use Contao\CoreBundle\Console\Application;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\System;

$root = getcwd();

// Der Autoloader zieht in Contao 4.13 thecodingmachine/safe herein, das
// unter PHP 8.4 eigene Deprecations wirft — die gehoeren nicht hierher
$stufe = error_reporting();
error_reporting($stufe & ~E_DEPRECATED);
require $root.'/vendor/autoload.php';
error_reporting($stufe);

$bundleDir = str_replace('\\', '/', realpath($root.'/vendor/schachbulle/contao-lizenzverwaltung-bundle'));

$befunde = array();

set_error_handler(static function ($no, $str, $file, $line) use ($bundleDir, &$befunde) {
	$file = str_replace('\\', '/', (string) $file);

	// Nur Meldungen aus dem eigenen Bundle; Contao selbst loest unter PHP 8.4
	// eine Reihe eigener Deprecations aus, die hier nichts zu suchen haben
	if (str_starts_with($file, $bundleDir)) {
		$befunde[] = sprintf('[%d] %s in %s:%d', $no, $str, substr($file, strlen($bundleDir) + 1), $line);
	}

	return true;
});

$kernel = Contao\ManagerBundle\HttpKernel\ContaoKernel::fromInput($root, new Symfony\Component\Console\Input\ArrayInput(array('--env' => 'prod')));
$kernel->boot();

$container = $kernel->getContainer();
System::setContainer($container);

// Einen Backend-Request unterschieben, damit Scope-Matcher und Sitzung greifen
$request = Symfony\Component\HttpFoundation\Request::create('/contao?do=lizenzverwaltung');
$request->attributes->set('_scope', 'backend');
$session = new Symfony\Component\HttpFoundation\Session\Session(new Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
$beBag = new Contao\CoreBundle\Session\Attribute\ArrayAttributeBag('_contao_be_attributes');
$beBag->setName('contao_backend');
$session->registerBag($beBag);
$request->setSession($session);
$container->get('request_stack')->push($request);

$container->get('contao.framework')->initialize();

// Der Token-Speicher wird sonst erst vom Kernel-Listener befuellt; der
// Dienst selbst ist privat, deshalb ueber den oeffentlichen Manager
$tm = $container->get('contao.csrf.token_manager');
$sp = new ReflectionProperty(Symfony\Component\Security\Csrf\CsrfTokenManager::class, 'storage');
$sp->setAccessible(true);
$speicher = $sp->getValue($tm);
if (method_exists($speicher, 'initialize')) { $speicher->initialize(array()); }

// Contao 4.13 legt in $_SESSION ein LazySessionAccess ab, das beim ersten
// Zugriff eine native Sitzung starten will und auf der Kommandozeile abbricht.
// initialize() setzt es, deshalb erst hier ueberschreiben.
$_SESSION = array();

$ok   = array();
$fehl = array();

function pruefe(string $name, callable $fn): void
{
	global $ok, $fehl;

	try {
		$fn();
		$ok[] = $name;
	} catch (\Throwable $e) {
		$fehl[] = $name.': '.get_class($e).' — '.$e->getMessage().' ('.basename($e->getFile()).':'.$e->getLine().')';
	}
}

$tabellen = array(
	'tl_lizenzverwaltung',
	'tl_lizenzverwaltung_items',
	'tl_lizenzverwaltung_mails',
	'tl_lizenzverwaltung_referenten',
	'tl_lizenzverwaltung_templates',
	'tl_lizenzverwaltung_verbaende',
	'tl_module',
	'tl_settings',
);

foreach ($tabellen as $tabelle) {
	pruefe('DCA laden: '.$tabelle, static function () use ($tabelle) {
		Controller::loadDataContainer($tabelle);
		System::loadLanguageFile($tabelle, 'de');

		if (empty($GLOBALS['TL_DCA'][$tabelle])) {
			throw new \RuntimeException('DCA ist leer');
		}
	});
}

// Palettenfelder gegen die Felddefinitionen pruefen
foreach ($tabellen as $tabelle) {
	pruefe('Palettenfelder: '.$tabelle, static function () use ($tabelle) {
		$dca = $GLOBALS['TL_DCA'][$tabelle] ?? array();

		foreach (($dca['palettes'] ?? array()) as $name => $palette) {
			// Bei Kerntabellen nur die eigene Palette pruefen, nicht die fremder Bundles
			if (\in_array($tabelle, array('tl_module', 'tl_settings'), true) && 'lizenzverwaltung' !== $name && 'default' !== $name) {
				continue;
			}

			if ('__selector__' === $name || !\is_string($palette)) {
				continue;
			}

			foreach (preg_split('/[;,]/', preg_replace('/\{[^}]*\}/', '', $palette)) as $feld) {
				$feld = trim($feld);

				if ('' !== $feld && !isset($dca['fields'][$feld])) {
					throw new \RuntimeException('Palette "'.$name.'" nennt das unbekannte Feld "'.$feld.'"');
				}
			}
		}
	});
}

// Pruefdaten anlegen
$db = Database::getInstance();
$db->prepare("DELETE FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen = ?")->execute('3');
$db->prepare("INSERT INTO tl_lizenzverwaltung_verbaende (tstamp, name, kennzeichen, organisation, published) VALUES (?,?,?,?,?)")
   ->execute(time(), 'Berlin', '3', serialize(array(array('organisation_name' => 'Testverband', 'organisation_id' => '4711'))), '1');

$db->prepare("DELETE FROM tl_lizenzverwaltung WHERE alias = ?")->execute('pruefstand');
$db->prepare("INSERT INTO tl_lizenzverwaltung (tstamp, alias, vorname, name, geburtstag, geschlecht, strasse, plz, ort, email, published) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
   ->execute(time(), 'pruefstand', 'Anna', 'Muster', (string) mktime(0, 0, 0, 5, 17, 1980), 'w', 'Teststr. 1', '10115', 'Berlin', 'anna@example.org', '1');
$personId = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung WHERE alias = ?")->limit(1)->execute('pruefstand')->id;

$db->prepare("INSERT INTO tl_lizenzverwaltung_items (pid, tstamp, verband, lizenz, erwerb, gueltigkeit, published, license_number_dosb, dosb_tstamp, dosb_code, dosb_antwort) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
   ->execute($personId, time(), '3', 'C', (string) mktime(0, 0, 0, 1, 1, 2022), (string) mktime(0, 0, 0, 12, 31, 2030), '1', 'DSchB-T-C-0000001', time(), 200, 'OK');
$lizenzId = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung_items WHERE pid = ?")->limit(1)->execute($personId)->id;

$db->prepare("INSERT INTO tl_lizenzverwaltung_templates (tstamp, name, description, template, published) VALUES (?,?,?,?,?)")
   ->execute(time(), 'Standard', 'Prüfstand', '<html><head>##css##</head><body><p>Hallo ##lizenz_vorname## ##lizenz_nachname##, Lizenz ##lizenz_art##.</p>##lizenz_content##</body></html>', '1');
$tplId = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung_templates WHERE name = ?")->limit(1)->execute('Standard')->id;

$db->prepare("INSERT INTO tl_lizenzverwaltung_mails (pid, tstamp, template, signatur, subject, content) VALUES (?,?,?,?,?,?)")
   ->execute($lizenzId, time(), $tplId, '1', 'Ihre Lizenz', '<p>Anbei die Urkunde.</p>');
$mailId = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung_mails WHERE pid = ?")->limit(1)->execute($lizenzId)->id;

$db->prepare("DELETE FROM tl_lizenzverwaltung_referenten WHERE email = ?")->execute('ref@example.org');
$db->prepare("INSERT INTO tl_lizenzverwaltung_referenten (tstamp, verband, nachname, vorname, email, published, sent_info) VALUES (?,?,?,?,?,?,?)")
   ->execute(time(), '3', 'Referent', 'Rudi', 'ref@example.org', '1', '1');

$H = 'Schachbulle\\ContaoLizenzverwaltungBundle\\Classes\\Helper';

pruefe('Helper::getProjectDir', static fn () => $H::getProjectDir() ?: throw new \RuntimeException('leer'));
pruefe('Helper::getSession', static fn () => $H::getSession() ?? throw new \RuntimeException('null'));
pruefe('Helper::getBackendSessionBag', static fn () => $H::getBackendSessionBag() ?? throw new \RuntimeException('null'));
pruefe('Helper::getRequestToken', static fn () => $H::getRequestToken() ?: throw new \RuntimeException('leer'));
pruefe('Helper::getBackendRoute', static fn () => $H::getBackendRoute() ?: throw new \RuntimeException('leer'));
pruefe('Helper::log', static fn () => $H::log('Prüfstand-Eintrag'));
pruefe('Helper::getVerbaende', static fn () => $H::getVerbaende()['3'] === 'Berlin' ?: throw new \RuntimeException('Berlin fehlt'));
pruefe('Helper::getVerband', static fn () => $H::getVerband('3') === 'Berlin' ?: throw new \RuntimeException('falsch'));
pruefe('Helper::getVerband (unbekannt)', static fn () => $H::getVerband('ZZ') === '' ?: throw new \RuntimeException('falsch'));
pruefe('Helper::getUntergliederung', static fn () => $H::getUntergliederung('3') === array('4711') ?: throw new \RuntimeException('falsch'));
pruefe('Helper::getLizenzen', static fn () => \count($H::getLizenzen()) === 11 ?: throw new \RuntimeException('Anzahl'));
pruefe('Helper::getVerlaengerung (leer)', static fn () => $H::getVerlaengerung(1000, null) === 1000 ?: throw new \RuntimeException('falsch'));
pruefe('Helper::getVerlaengerung (Liste)', static fn () => $H::getVerlaengerung(1000, serialize(array(array('datum' => 2000), array('datum' => 3000)))) === 3000 ?: throw new \RuntimeException('falsch'));
pruefe('Helper::getVerbandMail', static fn () => $H::getVerbandMail('3') === 'ref@example.org' ?: throw new \RuntimeException('falsch'));
pruefe('Helper::getPersonMail', static fn () => $H::getPersonMail($personId) === 'anna@example.org' ?: throw new \RuntimeException('falsch'));
pruefe('Helper::sortArrayByFields', static function () use ($H) {
	$r = $H::sortArrayByFields(array(array('n' => 3), array('n' => 1), array('n' => 2)), array('n' => SORT_ASC));

	if (array_column($r, 'n') !== array(1, 2, 3)) {
		throw new \RuntimeException('falsch sortiert');
	}
});
pruefe('Helper::sortArrayByFields (leer)', static fn () => $H::sortArrayByFields(array(), array('n' => SORT_ASC)) === array() ?: throw new \RuntimeException('falsch'));
pruefe('Helper::replaceTokens', static function () use ($H) {
	$r = $H::replaceTokens('Hallo ##name##!', array('name' => 'Welt'));

	if ('Hallo Welt!' !== $r) {
		throw new \RuntimeException('Ergebnis: '.$r);
	}
});

$L = 'Schachbulle\\ContaoLizenzverwaltungBundle\\Classes\\LimsClient';

pruefe('LimsClient::findRecord', static function () use ($L, $lizenzId) {
	$c = new $L();

	if (null === $c->findRecord($lizenzId)) {
		throw new \RuntimeException('nicht gefunden');
	}
});
pruefe('LimsClient::buildLicenseData', static function () use ($L, $lizenzId) {
	$c = new $L();
	$d = $c->buildLicenseData($c->findRecord($lizenzId));

	if ($d['training_course_id'] !== 513 || $d['custom_1'] !== 'Berlin' || $d['license_number_dosb'] !== 'DSchB-T-C-0000001') {
		throw new \RuntimeException('Datenpaket: '.json_encode($d));
	}
});
pruefe('LimsClient::request (ohne Adresse)', static function () use ($L) {
	$r = (new $L())->request('request');

	if (0 !== $r['code'] || null === $r['error']) {
		throw new \RuntimeException('unerwartet: '.json_encode($r));
	}
});
pruefe('LimsClient::transferLicense (ohne Adresse)', static function () use ($L, $lizenzId) {
	$r = (new $L())->transferLicense($lizenzId);

	if (0 !== $r['code']) {
		throw new \RuntimeException('unerwartet: '.json_encode($r));
	}
});
pruefe('LimsClient::migrateLicense (ohne Adresse)', static fn () => (new $L())->migrateLicense($lizenzId));

// DCA-Rueckrufklassen
$dcTraeger = new class($lizenzId) {
	public function __construct(public $id) {}
	public $table = 'tl_lizenzverwaltung_items';
	public $activeRecord = null;
};

pruefe('tl_lizenzverwaltung_items instanziieren', static fn () => new tl_lizenzverwaltung_items());
pruefe('tl_lizenzverwaltung instanziieren', static fn () => new tl_lizenzverwaltung());
pruefe('tl_lizenzverwaltung_mails instanziieren', static fn () => new tl_lizenzverwaltung_mails());
pruefe('tl_lizenzverwaltung_referenten instanziieren', static fn () => new tl_lizenzverwaltung_referenten());
pruefe('tl_lizenzverwaltung_templates instanziieren', static fn () => new tl_lizenzverwaltung_templates());
pruefe('tl_lizenzverwaltung_verbaende instanziieren', static fn () => new tl_lizenzverwaltung_verbaende());

$dcItems = new Contao\DC_Table('tl_lizenzverwaltung_items');
$reflId  = new ReflectionProperty(Contao\DataContainer::class, 'intId');
$reflId->setAccessible(true);
$reflId->setValue($dcItems, $lizenzId);

$items = new tl_lizenzverwaltung_items();

foreach (array('getLizenznummer', 'getLizenzbutton', 'getLizenzPDF', 'getLizenzPDFCard', 'setHeute', 'getLeitfaden', 'getVerification', 'viewEnclosureInfo', 'viewPDF', 'viewPDFCard') as $m) {
	pruefe('tl_lizenzverwaltung_items::'.$m, static function () use ($items, $m, $dcItems) {
		$r = $items->$m($dcItems);

		if (!\is_string($r)) {
			throw new \RuntimeException('kein String, sondern '.get_debug_type($r));
		}
	});
}

pruefe('tl_lizenzverwaltung_items::listLizenzen', static function () use ($items, $db, $lizenzId) {
	$row = $db->prepare("SELECT * FROM tl_lizenzverwaltung_items WHERE id = ?")->execute($lizenzId)->row();
	$items->listLizenzen($row);
});
pruefe('tl_lizenzverwaltung_items::toggleEmail', static function () use ($items, $db, $lizenzId) {
	$row = $db->prepare("SELECT * FROM tl_lizenzverwaltung_items WHERE id = ?")->execute($lizenzId)->row();
	$items->toggleEmail($row, 'table=tl_lizenzverwaltung_mails', 'E-Mails', 'E-Mails', 'email.png', '');
});
pruefe('tl_lizenzverwaltung_items::getQuartalsende', static function () use ($items) {
	$r = $items->getQuartalsende(mktime(0, 0, 0, 3, 5, 2026));

	if ($r !== mktime(0, 0, 0, 12, 31, 2026)) {
		throw new \RuntimeException('falsch: '.date('d.m.Y', $r));
	}
});

$dcPerson = new Contao\DC_Table('tl_lizenzverwaltung');
$reflId->setValue($dcPerson, $personId);

$person = new tl_lizenzverwaltung();

pruefe('tl_lizenzverwaltung::generateAdvancedFilter', static fn () => $person->generateAdvancedFilter($dcPerson));
pruefe('tl_lizenzverwaltung::applyAdvancedFilter', static fn () => $person->applyAdvancedFilter());
pruefe('tl_lizenzverwaltung::viewEnclosureInfo', static fn () => $person->viewEnclosureInfo($dcPerson));
pruefe('tl_lizenzverwaltung::viewLabels', static function () use ($person, $db, $personId, $dcPerson) {
	$row  = $db->prepare("SELECT * FROM tl_lizenzverwaltung WHERE id = ?")->execute($personId)->row();
	$args = $person->viewLabels($row, '', $dcPerson, array('Muster', 'Anna', '', '', '', ''));

	if ('' === $args[4] || 'Berlin' !== $args[5]) {
		throw new \RuntimeException('Spalten: '.json_encode($args));
	}
});
pruefe('tl_lizenzverwaltung::generateAlias', static function () use ($person, $dcPerson, $db, $personId) {
	$person->generateAlias($dcPerson);
	$alias = $db->prepare("SELECT alias FROM tl_lizenzverwaltung WHERE id = ?")->execute($personId)->alias;

	if ('muster-anna' !== $alias) {
		throw new \RuntimeException('Alias: '.$alias);
	}
});

$dcMail = new Contao\DC_Table('tl_lizenzverwaltung_mails');
$reflId->setValue($dcMail, $mailId);

$mails = new tl_lizenzverwaltung_mails();

pruefe('tl_lizenzverwaltung_mails::getTemplates', static function () use ($mails, $dcMail, $tplId) {
	$o = $mails->getTemplates($dcMail);

	if (!isset($o[$tplId])) {
		throw new \RuntimeException('Vorlage fehlt: '.json_encode($o));
	}
});
pruefe('tl_lizenzverwaltung_mails::getPreview', static function () use ($mails, $dcMail) {
	$r = $mails->getPreview($dcMail);

	if (!str_contains($r, 'Anna') || !str_contains($r, 'Anbei die Urkunde')) {
		throw new \RuntimeException('Vorschau unvollständig: '.strip_tags($r));
	}
});
pruefe('tl_lizenzverwaltung_mails::listEmails', static function () use ($mails, $db, $mailId) {
	$row = $db->prepare("SELECT * FROM tl_lizenzverwaltung_mails WHERE id = ?")->execute($mailId)->row();
	$r   = $mails->listEmails($row);

	if (!str_contains($r, 'Nicht versendet')) {
		throw new \RuntimeException('Zeile: '.strip_tags($r));
	}
});

$dcRef = new Contao\DC_Table('tl_lizenzverwaltung_referenten');
$refId = (int) $db->prepare("SELECT id FROM tl_lizenzverwaltung_referenten WHERE email = ?")->limit(1)->execute('ref@example.org')->id;
$reflId->setValue($dcRef, $refId);

pruefe('tl_lizenzverwaltung_referenten::getLizenzversand', static fn () => (new tl_lizenzverwaltung_referenten())->getLizenzversand($dcRef));
pruefe('tl_lizenzverwaltung_verbaende::getRecord', static function () use ($db) {
	$row  = $db->prepare("SELECT * FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen = ?")->execute('3')->row();
	$args = (new tl_lizenzverwaltung_verbaende())->getRecord($row, '', new Contao\DC_Table('tl_lizenzverwaltung_verbaende'), array('Berlin', '3', ''));

	if (!str_contains($args[2], 'Testverband')) {
		throw new \RuntimeException('Spalte: '.$args[2]);
	}
});

// Klassen des Backend-Moduls
pruefe('Mailer::getPreview', static function () use ($mailId, $lizenzId, $tplId) {
	$m = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\Mailer();
	$r = $m->getPreview($mailId, $lizenzId, $tplId, false);

	if (!str_contains($r, 'Hallo Anna Muster')) {
		throw new \RuntimeException('Vorschau: '.strip_tags($r));
	}
});
pruefe('Mailer::validateEmail', static function () {
	$m = 'Schachbulle\\ContaoLizenzverwaltungBundle\\Classes\\Mailer';

	if (!$m::validateEmail('Anna Muster <anna@example.org>') || $m::validateEmail('kaputt')) {
		throw new \RuntimeException('falsch');
	}
});
pruefe('TrainerlizenzExport::getRecords', static function () use ($dcPerson) {
	$e = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzExport();
	$r = $e->getRecords($dcPerson);

	if (!$r || 'Muster' !== $r[0]['name'] || 'Berlin' !== $r[0]['verband']) {
		throw new \RuntimeException('Datensätze: '.json_encode($r));
	}
});
pruefe('TrainerlizenzExport::exportTrainer_XLS (falscher Schlüssel)', static function () use ($dcPerson) {
	$e = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzExport();

	if ('' !== $e->exportTrainer_XLS($dcPerson)) {
		throw new \RuntimeException('sollte leer sein');
	}
});
pruefe('Marker::deleteMarker (falscher Schlüssel)', static function () use ($dcItems) {
	$m = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\Marker();

	if ('' !== $m->deleteMarker($dcItems)) {
		throw new \RuntimeException('sollte leer sein');
	}
});
pruefe('TrainerlizenzImport::importTrainer (falscher Schlüssel)', static function () use ($dcPerson) {
	$i = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzImport();

	if ('' !== $i->importTrainer($dcPerson)) {
		throw new \RuntimeException('sollte leer sein');
	}
});
pruefe('DOSBLizenzen::exportToDOSB (Startseite)', static function () {
	$d = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen();
	$r = $d->exportToDOSB();

	if (!str_contains($r, 'Export starten')) {
		throw new \RuntimeException('Seite: '.strip_tags($r));
	}
});
pruefe('DOSBLizenzen::exportToDOSB (Stapellauf)', static function () {
	Contao\Input::setGet('start', '1');
	$d = new Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen();
	$r = $d->exportToDOSB();
	Contao\Input::setGet('start', null);

	if (!str_contains($r, '_lizenzverwaltung/lims/export/')) {
		throw new \RuntimeException('Route fehlt in der Ausgabe');
	}
});

// Frontend-Modul
pruefe('Lizenzenliste (Frontend)', static function () use ($container) {
	$modul = $container->getParameter('kernel.project_dir');
	$row   = array('id' => 1, 'type' => 'lizenzverwaltung', 'headline' => serialize(array('unit' => 'h2', 'value' => 'Lizenzen')), 'lizenzverwaltung_typ' => serialize(array('C')), 'lizenzverwaltung_typview' => '1', 'lizenzverwaltung_endofyear' => '');
	$m     = new Schachbulle\ContaoLizenzverwaltungBundle\Modules\Lizenzenliste(new Contao\ModuleModel());

	$refl = new ReflectionMethod($m, 'compile');
	$refl->setAccessible(true);

	foreach ($row as $k => $v) {
		$m->$k = $v;
	}

	$tpl = new Contao\FrontendTemplate('mod_lizenzenliste');
	$m->Template = $tpl;

	$refl->invoke($m);

	if (!\is_array($tpl->trainer) || !$tpl->trainer) {
		throw new \RuntimeException('keine Lizenzen im Template');
	}

	if ('Muster' !== $tpl->trainer[0]['nachname'] || 'Berlin' !== $tpl->trainer[0]['verband']) {
		throw new \RuntimeException('Inhalt: '.json_encode($tpl->trainer[0]));
	}
});

// Aufraeumen
$db->prepare("DELETE FROM tl_lizenzverwaltung_mails WHERE pid = ?")->execute($lizenzId);
$db->prepare("DELETE FROM tl_lizenzverwaltung_items WHERE pid = ?")->execute($personId);
$db->prepare("DELETE FROM tl_lizenzverwaltung WHERE id = ?")->execute($personId);
$db->prepare("DELETE FROM tl_lizenzverwaltung_templates WHERE id = ?")->execute($tplId);
$db->prepare("DELETE FROM tl_lizenzverwaltung_referenten WHERE email = ?")->execute('ref@example.org');
$db->prepare("DELETE FROM tl_lizenzverwaltung_verbaende WHERE kennzeichen = ?")->execute('3');

restore_error_handler();

echo "\n=== ERGEBNIS ===\n";
echo \count($ok)." Prüfungen bestanden\n";

if ($fehl) {
	echo "\n".\count($fehl)." FEHLER:\n";
	foreach ($fehl as $f) {
		echo "  - $f\n";
	}
}

if ($befunde) {
	echo "\n".\count(array_unique($befunde))." MELDUNGEN aus dem Bundle:\n";
	foreach (array_unique($befunde) as $b) {
		echo "  - $b\n";
	}
}

if (!$fehl && !$befunde) {
	echo "\nKeine Fehler, keine Meldungen.\n";
}

exit($fehl || $befunde ? 1 : 0);
