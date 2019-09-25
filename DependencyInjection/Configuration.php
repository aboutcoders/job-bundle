<?php

namespace Abc\JobBundle\DependencyInjection;

use Abc\Job\Symfony\MissingComponentFactory;
use Abc\Scheduler\Scheduler;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('abc_job');

        $rootNode
            ->children()
                ->scalarNode('transport')->defaultValue('default')->end()
                ->scalarNode('default_queue')->defaultValue('default')->end()
                ->scalarNode('default_replyTo')->defaultValue('reply')->end()
                ->scalarNode('prefix')->defaultValue('abc')->end()
                ->scalarNode('separator')->defaultValue('.')->end()
                ->append($this->getSchedulerConfiguration())
            ->end();

        return $treeBuilder;
    }

    private function getSchedulerConfiguration(): ArrayNodeDefinition
    {
        if (false === class_exists(Scheduler::class)) {
            return MissingComponentFactory::getConfiguration('scheduler', ['abc/scheduler-bundle']);
        }

        return (new ArrayNodeDefinition('scheduler'))
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ;
    }
}
