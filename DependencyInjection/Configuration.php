<?php

namespace ITF\IpInfoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ip_info');

        $rootNode
            ->children()
                ->scalarNode('access_key')->defaultNull()->end()
                ->booleanNode('use_rate_limit')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
