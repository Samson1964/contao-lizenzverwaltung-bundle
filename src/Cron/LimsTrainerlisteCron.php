<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Cron;

use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsTrainerliste;

/**
 * Ruft die Trainerlisten einmal täglich über Contaos Cron neu aus dem LiMS ab.
 *
 * Der Vollabruf dauert über alle Untergliederungen des DSB rund 16 Sekunden
 * (gemessen am 2026-09-15). Ohne diesen Job müsste nach Ablauf des
 * Wochen-Caches der erste Besucher der Trainerliste so lange warten.
 *
 * Der Job läuft bewusst auch im Scope „web“: Contao startet den Web-Cron in
 * beiden Fassungen erst im kernel.terminate, also nachdem die Antwort an den
 * Besucher gesendet ist (CommandSchedulerListener). Würde der Web-Scope
 * ausgeschlossen, liefe der Job auf Servern ohne `contao:cron`-Systemcron nie.
 *
 * Registriert über den Service-Tag `contao.cronjob` in der services.yml statt
 * über `#[AsCronJob]`, damit Contao 4.13 und 5 dieselbe Schreibweise lesen.
 */
class LimsTrainerlisteCron
{
	/**
	 * Erzeugt den Job.
	 *
	 * @param LimsTrainerliste $liste Der Abrufdienst mit Cache
	 */
	public function __construct(private readonly LimsTrainerliste $liste)
	{
	}

	/**
	 * Ruft alle Trainerlisten frisch ab und ersetzt den Cache.
	 *
	 * Ein Abruf gilt für alle vier Listen, deshalb genügt ein Aufruf mit einer
	 * beliebigen Art. Schlägt der Abruf fehl, protokolliert der Dienst das und
	 * behält die bisherige Fassung; der Job selbst wirft dann nichts, damit die
	 * übrigen Cronjobs von Contao weiterlaufen.
	 *
	 * @param string $scope „web“ oder „cli“, von Contao übergeben; wird nicht
	 *                      ausgewertet, siehe Klassenkommentar
	 *
	 * @return void Unter Contao 5 muss ein Cronjob null oder ein Promise
	 *              liefern; void erfüllt das
	 */
	public function __invoke(string $scope): void
	{
		$this->liste->getListe('A', true);
	}
}
