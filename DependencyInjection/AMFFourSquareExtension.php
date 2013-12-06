<?php

namespace AMF\FourSquareBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AMFFourSquareExtension extends Extension {

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('foursquare.yml');

        if (!empty($config['settings'])) 
        {
            $this->remapParametersNamespaces($config, $container, array(
                'settings' => 'amf_foursquare.settings.%s',
            ));
        }
    }

    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces) {
        foreach ($namespaces as $namespace => $map) {
            if ($namespace) {
                if (!array_key_exists($namespace, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$namespace];
            } else {
                $namespaceConfig = $config;
            }
            foreach ($namespaceConfig as $name => $value) {
                $container->setParameter(sprintf($map, $name), $value);
            }
        }
    }

}
