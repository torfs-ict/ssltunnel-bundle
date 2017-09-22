<?php

namespace TorfsICT\StunnelBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('torfs_ict_stunnel');

        $rootNode->children()
            ->scalarNode('accept_host')->end()
            ->scalarNode('forward_host')->end()
            ->integerNode('forward_port')->defaultValue(80)->end()
            ->scalarNode('fullchain')->isRequired()->end()
            ->scalarNode('privkey')->isRequired()->end()
            ->booleanNode('nanobox')->defaultFalse()->end()
        ;

        return $treeBuilder;
    }
}
