<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Schachbulle\ContaoLizenzverwaltungBundle\ContaoLizenzverwaltungBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Meldet das Bundle beim Contao Manager an.
 */
class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
	/**
	 * Liefert die Bundle-Konfiguration.
	 *
	 * Das Bundle wird nach dem Contao-Kern geladen, damit dessen DCA-Dateien
	 * und Konfiguration bereits stehen, wenn die eigenen Dateien greifen.
	 *
	 * @param ParserInterface $parser Wird für dieses Bundle nicht gebraucht,
	 *                                gehört aber zur Schnittstelle
	 *
	 * @return array<int,BundleConfig> Die Konfiguration dieses einen Bundles
	 */
	public function getBundles(ParserInterface $parser): array
	{
		return array
		(
			BundleConfig::create(ContaoLizenzverwaltungBundle::class)
				->setLoadAfter(array(ContaoCoreBundle::class)),
		);
	}

	/**
	 * Lädt die Routen des Bundles.
	 *
	 * Die Routen bedienen die Einzelaufrufe des Stapelexports zum DOSB. Sie
	 * ersetzen die früheren PHP-Dateien unter `Resources/public/`, die sich
	 * das Contao-Framework über `system/initialize.php` besorgten — eine
	 * Datei, die es unter Contao 5 nicht mehr gibt.
	 *
	 * @param LoaderResolverInterface $resolver Sucht den passenden Lader für
	 *                                          die YAML-Datei heraus
	 * @param KernelInterface         $kernel   Wird nicht ausgewertet, gehört
	 *                                          aber zur Schnittstelle
	 *
	 * @return RouteCollection|null Die Routen, oder null wenn sich für die
	 *                              Datei kein Lader finden lässt
	 */
	public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
	{
		$file = __DIR__.'/../Resources/config/routing.yml';

		$loader = $resolver->resolve($file);

		if (false === $loader)
		{
			return null;
		}

		return $loader->load($file);
	}
}
