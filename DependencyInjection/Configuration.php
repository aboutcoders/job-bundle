<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('abc_job');

        $supportedDrivers = ['orm', 'custom'];
        $supportedAdapters = ['bernard', 'sonata', 'custom'];
        $supportedLoggingHandlers = ['file', 'orm', 'custom'];

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($supportedDrivers))
                    ->end()
                    ->defaultValue('orm')
                ->end()
                ->scalarNode('adapter')
                    ->validate()
                        ->ifNotInArray($supportedAdapters)
                        ->thenInvalid('The adapter %s is not supported. Please choose one of ' . json_encode($supportedAdapters))
                    ->end()
                    ->isRequired()
                ->end()
                ->arrayNode('manager')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('validate')->defaultFalse()->end()
                    ->end()
                ->end()
                ->scalarNode('default_queue')->defaultValue('default')->end()
                ->arrayNode('queues')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->booleanNode('register_default_jobs')
                    ->defaultFalse()
                ->end()
                ->scalarNode('model_manager_name')
                    ->defaultNull()
                ->end()
                ->arrayNode('controller')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('refresh_interval')->defaultValue(1)->end()
                    ->end()
                ->end()
                ->arrayNode('rest')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable')->defaultTrue()->end()
                        ->scalarNode('validate')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('handler')
                            ->defaultValue('file')
                            ->validate()
                                ->ifNotInArray($supportedLoggingHandlers)
                                ->thenInvalid('The handler %s is not supported. Please choose one of ' . json_encode($supportedLoggingHandlers))
                            ->end()
                            ->cannotBeOverwritten()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('directory')->defaultValue('%kernel.logs_dir%')->end()
                        ->scalarNode('default_level')->defaultValue('info')->end()
                        ->arrayNode('processor')
                            ->canBeUnset()
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('custom_level')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        $this->addServiceSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addServiceSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('service')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('manager')->defaultValue('abc.job.manager.default')->end()
                            ->scalarNode('job_manager')->defaultValue('abc.job.job_manager.default')->end()
                            ->scalarNode('agent_manager')->defaultValue('abc.job.agent_manager.default')->end()
                            ->scalarNode('schedule_manager')->defaultValue('abc.job.schedule_manager.default')->end()
                            ->scalarNode('schedule_iterator')->defaultValue('abc.job.schedule_iterator.default')->end()
                            ->scalarNode('schedule_manager_iterator')->defaultValue('abc.job.schedule_manager_iterator.default')->end()
                            ->scalarNode('controller_factory')->defaultValue('abc.job.controller_factory.default')->end()
                            ->scalarNode('controller')->defaultValue('abc.process_control.controller')->end()
                            ->scalarNode('queue_config')->defaultValue('abc.job.queue_config.default')->end()
                            ->scalarNode('validator')->defaultValue('validator')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}