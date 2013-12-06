<?php

namespace AMF\FourSquareBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('amf_foursquare');
        
        $this->addSettingsSection($rootNode);

        return $treeBuilder;
    }

    public function addSettingsSection(ArrayNodeDefinition $node) {
        $node->children()
                ->arrayNode('settings')
                ->addDefaultsIfNotSet()
                ->canBeUnset()
                    ->children()
                        ->scalarNode('client_id')->defaultFalse()->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('client_secret')->defaultFalse()->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('client_locale')->defaultValue('fr')->cannotBeEmpty()->end()
                        ->scalarNode('version')->defaultValue('v2')->cannotBeEmpty()->end()
                        ->scalarNode('redirect_uri')->defaultNull()->end()
                    ->end()
                ->end();
    }
    
    public function addSearchSection(ArrayNodeDefinition $node) {
        $node->children()
                ->arrayNode('search')
                ->addDefaultsIfNotSet()
                ->canBeUnset()
                    ->children()
                        ->scalarNode('ll')->defaultFalse()->end()
                        ->scalarNode('near')->defaultFalse()->end()
                        ->scalarNode('llAcc')->defaultValue('20120228')->cannotBeEmpty()->end()
                        ->scalarNode('alt')->defaultValue(0)->end()
                        ->scalarNode('altAcc')->defaultNull()->end()
                        ->scalarNode('query')->defaultNull()->end()
                        ->scalarNode('limit')->defaultNull()->end()
                        ->scalarNode('intent')->defaultNull()->end()
                        ->scalarNode('radius')->defaultNull()->end()
                        ->scalarNode('sw')->defaultNull()->end()
                        ->scalarNode('ne')->defaultNull()->end()
                        ->scalarNode('categoryId')->defaultNull()->end()
                        ->scalarNode('url')->defaultNull()->end()
                        ->scalarNode('providerId')->defaultNull()->end()
                        ->scalarNode('linkedId')->defaultNull()->end()
                    ->end()
                ->end();
    }

}
