<?php

/**
 * Prüfstand für die Trainerliste aus dem LiMS.
 *
 * Aufruf aus dem Wurzelverzeichnis einer Contao-Installation:
 *   php <pfad-zum-bundle>/tests/pruefstand-lims.php
 *
 * Es wird **kein** Aufruf an das echte LiMS geschickt: Ein nachgebildeter
 * Client liefert feste Antworten, ein Speicher-Cache ersetzt cache.app. Geprüft
 * werden Blättern, Verbandsnamen über mehrere Ebenen, der Rückfall auf
 * custom_1, das Übergehen anonymisierter Lizenzen, Sortierung, Cache und die
 * Reserve bei einem fehlgeschlagenen Abruf. Zum Schluss wird das Modul samt
 * Template in der echten Installation gerendert.
 */

use Contao\Controller;
use Contao\System;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsClient;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsTrainerliste;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

$root = getcwd();

$stufe = error_reporting();
error_reporting($stufe & ~E_DEPRECATED);
require $root.'/vendor/autoload.php';
error_reporting($stufe);

$bundleDir = str_replace('\\', '/', realpath($root.'/vendor/schachbulle/contao-lizenzverwaltung-bundle'));
$befunde   = array();

set_error_handler(static function ($no, $str, $file, $line) use ($bundleDir, &$befunde) {
	$file = str_replace('\\', '/', (string) $file);

	if (str_starts_with($file, $bundleDir)) {
		$befunde[] = sprintf('[%d] %s in %s:%d', $no, $str, substr($file, \strlen($bundleDir) + 1), $line);
	}

	return true;
});

$kernel = Contao\ManagerBundle\HttpKernel\ContaoKernel::fromInput($root, new Symfony\Component\Console\Input\ArrayInput(array('--env' => 'prod')));
$kernel->boot();

$container = $kernel->getContainer();
System::setContainer($container);

$request = Symfony\Component\HttpFoundation\Request::create('/trainer.html');
$request->attributes->set('_scope', 'frontend');
$container->get('request_stack')->push($request);
$container->get('contao.framework')->initialize();
$_SESSION = array();

/**
 * Nachgebildeter LiMS-Client mit festen Antworten.
 */
class FakeLimsClient extends LimsClient
{
	/** @var array<int,string> Protokoll der Aufrufe */
	public array $aufrufe = array();

	/** Liefert bei true für jeden Aufruf HTTP 500 */
	public bool $kaputt = false;

	/**
	 * Beantwortet einen Aufruf aus den festen Daten.
	 *
	 * @param string                   $method  Pfad
	 * @param array<string,mixed>|null $data    Formularfelder
	 * @param int                      $timeout Unbenutzt
	 *
	 * @return array{code:int,body:string,error:string|null}
	 */
	public function request(string $method, ?array $data = null, int $timeout = 30): array
	{
		$this->aufrufe[] = $method.' '.json_encode($data);

		if ($this->kaputt) {
			return array('code' => 500, 'body' => 'Server kaputt', 'error' => null);
		}

		if ('lookup_organisations' === $method) {
			$kinder = array(
				1093 => array(array('id' => '5001', 'title' => 'Landesschachverband Sachsen-Anhalt')),
				5001 => array(array('id' => '6001', 'title' => 'Schachbezirk Dessau')),
			);

			$liste = $kinder[(int) $data['organisation_parent_id']] ?? array();

			return array('code' => 200, 'body' => json_encode(array('size' => \count($liste), 'offset' => 0, 'total' => \count($liste), 'organisations' => $liste)), 'error' => null);
		}

		if ('lookup' === $method) {
			$alle = array(
				array('firstname' => 'Zora', 'lastname' => 'Zeller', 'training_course_id' => 515, 'organisation_id' => 6001, 'valid_until' => mktime(12, 0, 0, 12, 31, 2027)),
				array('firstname' => 'Ärne', 'lastname' => 'Ätzel', 'training_course_id' => 71011, 'organisation_id' => 9999, 'custom_1' => 'Saarland', 'valid_until' => mktime(12, 0, 0, 12, 31, 2026)),
				array('firstname' => '', 'lastname' => '', 'training_course_id' => 515, 'organisation_id' => 6001, 'valid_until' => mktime(12, 0, 0, 12, 31, 2027)),
				array('firstname' => 'Bea', 'lastname' => 'Bauer', 'training_course_id' => 514, 'organisation_id' => 5001, 'valid_until' => mktime(12, 0, 0, 12, 31, 2028)),
				array('firstname' => 'Anton', 'lastname' => 'Adler', 'training_course_id' => 49337, 'organisation_id' => 1093, 'valid_until' => mktime(12, 0, 0, 6, 30, 2029)),
			);

			// Zwei Datensätze je Seite, damit das Blättern geprüft wird
			$teil = \array_slice($alle, (int) $data['offset'], 2);

			return array('code' => 200, 'body' => json_encode(array('size' => \count($teil), 'offset' => (int) $data['offset'], 'total' => \count($alle), 'licenses' => $teil)), 'error' => null);
		}

		return array('code' => 404, 'body' => '', 'error' => null);
	}
}

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

function gleich($ist, $soll, string $was): void
{
	if ($ist !== $soll) {
		throw new \RuntimeException($was.': erwartet '.json_encode($soll, JSON_UNESCAPED_UNICODE).', erhalten '.json_encode($ist, JSON_UNESCAPED_UNICODE));
	}
}

$client = new FakeLimsClient();
$cache  = new ArrayAdapter();
$dienst = new LimsTrainerliste($client, $cache);

pruefe('A-Trainer: Inhalt, Sortierung, anonymisierte übergangen', static function () use ($dienst) {
	$l = $dienst->getListe('A');

	gleich(array_column($l['eintraege'], 'nachname'), array('Ätzel', 'Zeller'), 'Nachnamen');
	gleich($l['veraltet'], false, 'veraltet');

	if ($l['stand'] < time() - 5) {
		throw new \RuntimeException('Stand fehlt');
	}
});

pruefe('Verbandsname über zwei Ebenen', static fn () => gleich($dienst->getListe('A')['eintraege'][1]['verband'], 'Schachbezirk Dessau', 'Verband Zeller'));
pruefe('Rückfall auf custom_1', static fn () => gleich($dienst->getListe('A')['eintraege'][0]['verband'], 'Saarland', 'Verband Ätzel'));
pruefe('B-Trainer', static fn () => gleich(array_column($dienst->getListe('B')['eintraege'], 'verband'), array('Landesschachverband Sachsen-Anhalt'), 'B-Liste'));
pruefe('C-Trainer leer', static fn () => gleich($dienst->getListe('C')['eintraege'], array(), 'C-Liste'));
pruefe('DOSB-Ausbilder', static fn () => gleich(array_column($dienst->getListe('AB')['eintraege'], 'gueltig_bis'), array(mktime(12, 0, 0, 6, 30, 2029)), 'AB-Liste'));
pruefe('Unbekannte Art', static fn () => gleich($dienst->getListe('XY')['eintraege'], array(), 'XY'));

pruefe('Geblättert: drei lookup-Seiten beim ersten Abruf', static function () use ($client) {
	gleich(\count(array_filter($client->aufrufe, static fn ($a) => str_starts_with($a, 'lookup {'))), 3, 'lookup-Aufrufe');
});

pruefe('Cache: weitere Listen lösen keinen Abruf aus', static function () use ($client, $dienst) {
	$vorher = \count($client->aufrufe);
	$dienst->getListe('B');
	$dienst->getListe('AB');
	gleich(\count($client->aufrufe), $vorher, 'Aufrufe');
});

pruefe('Fehlschlag: Reserve wird ausgeliefert', static function () use ($client, $dienst) {
	$client->kaputt = true;
	$l = $dienst->getListe('A', true);
	$client->kaputt = false;

	gleich($l['veraltet'], true, 'veraltet');
	gleich(array_column($l['eintraege'], 'nachname'), array('Ätzel', 'Zeller'), 'Reserve');
});

pruefe('Fehlschlag ohne Reserve: leer, kein Absturz', static function () {
	$c = new FakeLimsClient();
	$c->kaputt = true;
	$l = (new LimsTrainerliste($c, new ArrayAdapter()))->getListe('A');

	gleich($l['eintraege'], array(), 'Einträge');
	gleich($l['stand'], 0, 'Stand');
	gleich($l['veraltet'], true, 'veraltet');
});

pruefe('findeListe: Liste unter unbekanntem Schlüssel', static fn () => gleich(LimsTrainerliste::findeListe(array('size' => 1, 'total' => 1, 'eintraege_xy' => array(array('a' => 1))), array('licenses')), array(array('a' => 1)), 'Liste'));
pruefe('findeListe: Antwort selbst ist Liste', static fn () => gleich(LimsTrainerliste::findeListe(array(array('a' => 1)), array()), array(array('a' => 1)), 'Liste'));
pruefe('findeListe: leere Antwort', static fn () => gleich(LimsTrainerliste::findeListe(array('size' => 0, 'total' => 0), array('licenses')), array(), 'Liste'));

pruefe('Dienst im Container öffentlich', static function () use ($container) {
	if (!$container->get(LimsTrainerliste::class) instanceof LimsTrainerliste) {
		throw new \RuntimeException('falscher Typ');
	}
});

pruefe('Modul registriert', static fn () => gleich($GLOBALS['FE_MOD']['application']['lizenzverwaltung_lims'] ?? null, Schachbulle\ContaoLizenzverwaltungBundle\Modules\LimsTrainerliste::class, 'FE_MOD'));

pruefe('Palette und Feld in tl_module', static function () {
	Controller::loadDataContainer('tl_module');
	System::loadLanguageFile('tl_module', 'de');

	$palette = $GLOBALS['TL_DCA']['tl_module']['palettes']['lizenzverwaltung_lims'] ?? '';

	foreach (preg_split('/[;,]/', preg_replace('/\{[^}]*\}/', '', $palette)) as $feld) {
		$feld = trim($feld);

		if ('' !== $feld && !isset($GLOBALS['TL_DCA']['tl_module']['fields'][$feld])) {
			throw new \RuntimeException('unbekanntes Feld '.$feld);
		}
	}

	$optionen = ($GLOBALS['TL_DCA']['tl_module']['fields']['lizenzverwaltung_lims_art']['options_callback'])();
	gleich($optionen, array('A' => 'A-Trainer', 'B' => 'B-Trainer', 'C' => 'C-Trainer', 'AB' => 'DOSB-Ausbilder'), 'Optionen');
});

pruefe('Modul rendert Template im Frontend', static function () use ($container, $cache, $client) {
	// Den echten Cache des Dienstes mit den Prüfdaten vorbelegen, damit das
	// Modul einen Treffer findet und kein Aufruf an das echte LiMS geht.
	// Lesen einer readonly-Eigenschaft per Reflection ist erlaubt, Schreiben nicht.
	$echt = $container->get(LimsTrainerliste::class);
	$p    = new ReflectionProperty(LimsTrainerliste::class, 'cache');
	$p->setAccessible(true);
	$echterCache = $p->getValue($echt);

	$quelle = $cache->getItem('lizenzverwaltung_lims_trainerliste');

	if (!$quelle->isHit()) {
		throw new \RuntimeException('Prüf-Cache leer');
	}

	$ziel = $echterCache->getItem('lizenzverwaltung_lims_trainerliste');
	$ziel->set($quelle->get())->expiresAfter(60);
	$echterCache->save($ziel);

	$model = new Contao\ModuleModel();
	$model->setRow(array('id' => 999, 'type' => 'lizenzverwaltung_lims', 'name' => 'Prüfstand', 'headline' => serialize(array('unit' => 'h2', 'value' => 'Liste der A-Trainer')), 'lizenzverwaltung_lims_art' => 'A', 'lizenzverwaltung_endofyear' => ''));

	$html = (new Schachbulle\ContaoLizenzverwaltungBundle\Modules\LimsTrainerliste($model))->generate();

	foreach (array('Liste der A-Trainer', 'Zeller', 'Schachbezirk Dessau', '31.12.2027', 'Ätzel', 'Saarland', 'Zuletzt aktualisiert: '.date('d.m.Y')) as $muss) {
		if (!str_contains($html, $muss)) {
			throw new \RuntimeException('fehlt im HTML: '.$muss."\n".$html);
		}
	}

	if (!str_contains($html, '&') && str_contains($html, '<script')) {
		throw new \RuntimeException('unerwartetes Skript');
	}
});

restore_error_handler();

echo "\n=== ERGEBNIS ===\n".\count($ok)." Prüfungen bestanden\n";

foreach ($fehl as $f) {
	echo "FEHLER: $f\n";
}

foreach (array_unique($befunde) as $b) {
	echo "MELDUNG: $b\n";
}

if (!$fehl && !$befunde) {
	echo "\nKeine Fehler, keine Meldungen.\n";
}

exit($fehl || $befunde ? 1 : 0);
