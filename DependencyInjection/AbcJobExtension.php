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

use Abc\ProcessControl\NullController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AbcJobExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $loader        = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));

        foreach ($config['service'] as $key => $service) {
            if (null !== $service) {
                $container->setAlias('abc.job.' . $key, $service);
            }
        }

        $container->setParameter('abc.job.controller_service', $config['service']['controller']);
        $container->setParameter('abc.job.locker_service', $config['service']['locker']);

        $this->remapParametersNamespaces(
            $config,
            $container,
            array(
                '' => array(
                    'adapter'               => 'abc.job.adapter',
                    'model_manager_name'    => 'abc.job.model_manager_name',
                    'register_default_jobs' => 'abc.job.register_default_jobs',
                    'queues'                => 'abc.job.queue_config',
                    'default_queue'         => 'abc.job.default_queue',
                )
            )
        );

        $this->remapParametersNamespaces(
            $config,
            $container,
            array(
                'logging' => array(
                    'directory' => 'abc.job.logging.directory',
                )
            )
        );

        $this->remapParametersNamespaces(
            $config,
            $container,
            array(
                'controller' => array(
                    'refresh_interval' => 'abc.job.controller.refresh_interval'
                )
            )
        );

        if ('custom' !== $config['db_driver']) {
            $loader->load(sprintf('%s.xml', $config['db_driver']));
        }

        if ('custom' !== $config['adapter']) {
            $loader->load(sprintf('adapter_%s.xml', $config['adapter']));
        }

        $this->loadManager($config, $loader, $container);
        $this->loadRest($config, $loader, $container);
        $this->loadDefaultJobs($config, $loader, $container);
        $this->loadLogger($config, $loader, $container);

        $loader->load('agent.xml');
        $loader->load('scheduler.xml');
        $loader->load('validator.xml');
        $loader->load('commands.xml');
        $loader->load('serializer.xml');
        $loader->load('locker.xml');
    }

    /**
     * @param array            $config
     * @param XmlFileLoader    $loader
     * @param ContainerBuilder $container
     */
    private function loadManager(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('registry.xml');
        $loader->load('manager.xml');
        $loader->load('listener.xml');

        $container->getDefinition('abc.job.manager.default')->replaceArgument(8, !$config['manager']['validate'] ? null : new Reference('abc.job.validator'));
    }

    /**
     * @param array            $config
     * @param XmlFileLoader    $loader
     * @param ContainerBuilder $container
     */
    private function loadLogger(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ('custom' !== $config['logging']['handler']) {
            $loader->load('logger_' . $config['logging']['handler'] . '.xml');

            if ('orm' == $config['logging']['handler']) {
                $container->setParameter('abc.job.register_mapping.' . $config['db_driver'], true);
            }
        }

        if (!empty($config['logging']['processor'])) {
            $factory = $container->getDefinition('abc.job.logger.factory');

            foreach ($config['logging']['processor'] as $serviceId) {
                $factory->addMethodCall('addProcessor', array(new Reference($serviceId)));
            }
        }

        $container->setParameter('abc.job.logging.default_level', $config['logging']['default_level']);
        $container->setParameter('abc.job.logging.custom_level', $config['logging']['custom_level']);
    }

    /**
     * @param array            $config
     * @param XmlFileLoader    $loader
     * @param ContainerBuilder $container
     */
    private function loadRest(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter('abc.job.rest', $config['rest']['enable']);

        if ($config['rest']['enable']) {
            $loader->load('job_param_converter.xml');
        }

        $container->getDefinition('abc.job.job_param_converter')->replaceArgument(1, !$config['rest']['validate'] ? null : new Reference('abc.job.validator'));
    }

    /**
     * @param array            $config
     * @param XmlFileLoader    $loader
     * @param ContainerBuilder $container
     */
    private function loadDefaultJobs(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['register_default_jobs']) {
            $loader->load('default_jobs.xml');
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $map
     * @return void
     */
    private function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (array_key_exists($name, $config)) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $namespaces $supportedAdapters
     * @return void
     */
    private function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }
}