<?php

declare(strict_types=1);

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoLizenzverwaltungBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Bindet die Dienstdefinitionen des Bundles in den Container ein.
 */
class ContaoLizenzverwaltungExtension extends Extension
{
	/**
	 * Lädt die Dienstdefinitionen.
	 *
	 * @param array<int,array<string,mixed>> $mergedConfig Die zusammengeführte
	 *                                                     Bundle-Konfiguration; das
	 *                                                     Bundle wertet sie nicht aus
	 * @param ContainerBuilder               $container    Der zu befüllende Container
	 *
	 * @return void `ExtensionInterface::load()` deklariert in Symfony 5.4 wie
	 *              in 7.4 keinen Rückgabetyp; `void` ist hier erlaubt
	 */
	public function load(array $mergedConfig, ContainerBuilder $container): void
	{
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../Resources/config')
		);

		$loader->load('services.yml');
	}
}
