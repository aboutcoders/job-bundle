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
        $supportedLogStorages = ['file', 'orm', 'custom'];
        $supportedLogLevels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert'];

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($supportedDrivers))
                    ->end()
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
                        ->scalarNode('validate')->defaultTrue()->end()
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
                ->scalarNode('connection')
                    ->defaultValue('default')
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
                        ->arrayNode('storage_handler')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')
                                ->defaultValue('file')
                                ->validate()
                                    ->ifNotInArray($supportedLogStorages)
                                    ->thenInvalid('The storage type %s is not supported. Please choose one of ' . json_encode($supportedLogStorages))
                                ->end()
                                ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('path')->defaultValue('%kernel.logs_dir%')->end()
                                ->scalarNode('level')
                                    ->defaultValue('info')
                                    ->validate()
                                        ->ifNotInArray($supportedLogLevels)
                                        ->thenInvalid('The level %s is not supported. Please choose one of ' . json_encode($supportedLogLevels))
                                    ->end()
                                ->end()
                                ->booleanNode('bubble')->defaultValue(true)->end()
                                ->arrayNode('processor')
                                    ->canBeUnset()
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('stream_handler')
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('path')->defaultValue('%kernel.logs_dir%')->end()
                                ->scalarNode('level')
                                    ->defaultValue('info')
                                    ->validate()
                                        ->ifNotInArray($supportedLogLevels)
                                        ->thenInvalid('The level %s is not supported. Please choose one of ' . json_encode($supportedLogLevels))
                                    ->end()
                                ->end()
                                ->booleanNode('bubble')->defaultTrue()->end()
                                ->scalarNode('formatter')->defaultNull()->end()
                                ->arrayNode('processor')
                                    ->canBeUnset()
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('handler')
                            ->canBeUnset()
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('level')
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
                            ->scalarNode('locker')->defaultValue('abc.job.locker.default')->end()
                            ->scalarNode('queue_config')->defaultValue('abc.job.queue_config.default')->end()
                            ->scalarNode('serializer')->defaultValue('abc.job.serializer.default')->end()
                            ->scalarNode('validator')->defaultValue('validator')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}