<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Nimmt die Einzelaufrufe des Stapellaufs zum DOSB entgegen.
 *
 * Bis Fassung 4.3.6 lagen an dieser Stelle die Skripte ajaxRequest.php und
 * ajaxRequestUmzug.php unter `Resources/public/`. Sie holten sich die
 * Contao-Umgebung über `system/initialize.php` — eine Datei, die es unter
 * Contao 5 nicht mehr gibt, weshalb der Stapelexport dort kommentarlos
 * gescheitert wäre. Beide Skripte liefen zudem ohne jede Anmeldeprüfung: Wer
 * die Adresse kannte, konnte Lizenzdaten an den DOSB schicken. Dieser
 * Controller ersetzt sie und verlangt einen Backend-Benutzer mit Zugriff auf
 * das Modul "lizenzverwaltung".
 */
class LimsController
{
	/**
	 * Erzeugt den Controller.
	 *
	 * @param ContaoFramework               $framework Wird benötigt, um vor dem Zugriff
	 *                                                 auf Contao-Klassen wie Database und
	 *                                                 Config das Framework hochzufahren
	 * @param AuthorizationCheckerInterface $security  Prüft die Modulberechtigung
	 */
	public function __construct(
		private readonly ContaoFramework $framework,
		private readonly AuthorizationCheckerInterface $security,
	) {
	}

	/**
	 * Überträgt eine einzelne Lizenz an das LiMS.
	 *
	 * @param int $id Datensatz-ID in tl_lizenzverwaltung_items
	 *
	 * @return JsonResponse Die Felder css_id, color, text und datum, die das
	 *                      Skript der Stapelseite in die Zeile des Datensatzes
	 *                      einträgt
	 *
	 * @throws AccessDeniedHttpException Wenn kein Backend-Benutzer angemeldet
	 *                                   ist oder ihm das Modul fehlt
	 */
	public function export(int $id): JsonResponse
	{
		$this->denyUnlessBackendUser();

		return $this->respond($id, (new LimsClient())->transferLicense($id));
	}

	/**
	 * Meldet eine einzelne Lizenz beim DOSB in einen Unterverband um.
	 *
	 * @param int $id Datensatz-ID in tl_lizenzverwaltung_items
	 *
	 * @return JsonResponse Aufbau wie bei export()
	 *
	 * @throws AccessDeniedHttpException Wenn kein Backend-Benutzer angemeldet
	 *                                   ist oder ihm das Modul fehlt
	 */
	public function umzug(int $id): JsonResponse
	{
		$this->denyUnlessBackendUser();

		return $this->respond($id, (new LimsClient())->migrateLicense($id));
	}

	/**
	 * Stellt sicher, dass ein berechtigter Backend-Benutzer anfragt.
	 *
	 * @return void Fährt nebenbei das Contao-Framework hoch, weil LimsClient
	 *              anschließend auf Database und Config zugreift
	 *
	 * @throws AccessDeniedHttpException Wenn die Berechtigung fehlt
	 */
	private function denyUnlessBackendUser(): void
	{
		$this->framework->initialize();

		if (!$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'lizenzverwaltung'))
		{
			throw new AccessDeniedHttpException('Kein Zugriff auf das Modul Lizenzverwaltung.');
		}
	}

	/**
	 * Formt die Antwort für das Skript der Stapelseite.
	 *
	 * @param int                          $id     Datensatz-ID, aus der die Element-ID gebildet wird
	 * @param array{code:int,text:string}  $result Ergebnis des LiMS-Aufrufs
	 *
	 * @return JsonResponse Grün bei HTTP 200, sonst rot
	 */
	private function respond(int $id, array $result): JsonResponse
	{
		return new JsonResponse(array
		(
			'css_id' => '#export_'.$id,
			'color'  => 200 === $result['code'] ? '#008000' : '#CA0000',
			'text'   => ' ('.$result['code'].' '.$result['text'].')',
			'datum'  => '[<i>'.date('d.m.Y H:i:s').'</i>] ',
		));
	}
}
