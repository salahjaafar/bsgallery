<?php
/**
 * Fournisseur de services pour le plugin Simple Bootstrap Gallery.
 * Architecture Joomla 5/6.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Content\Bsgallery\Extension\Bsgallery;

return new class implements ServiceProviderInterface {
    /**
     * Enregistre le plugin dans le conteneur DI.
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                // Récupération du dispatcher et des paramètres du plugin
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = PluginHelper::getPlugin('content', 'bsgallery');
                
                // Instanciation de notre classe principale
                return new Bsgallery($dispatcher, (array) $plugin);
            }
        );
    }
};
