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

use Abc\Bundle\JobBundle\Form\Type\MessageType;
use Abc\Bundle\JobBundle\Form\Type\SecondsType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\AbstractType;

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

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));

        $this->configureServices($container, $config, [
            'manager',
            'job_manager',
            'agent_manager',
            'schedule_manager',
            'schedule_iterator',
            'schedule_manager_iterator',
            'controller_factory'
        ]);

        if ('custom' !== $config['db_driver']) {
            $loader->load(sprintf('%s.xml', $config['db_driver']));
        }

        if ('custom' !== $config['adapter']) {
            $loader->load(sprintf('%s.xml', $config['adapter']));
        }

        $this->remapParametersNamespaces(
            $config,
            $container,
            array(
                '' => array(
                    'model_manager_name'    => 'abc.job.model_manager_name',
                    'register_default_jobs' => 'abc.job.register_default_jobs',
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

        $loader->load('agent.xml');
        $loader->load('manager.xml');
        $loader->load('schedule.xml');
        $loader->load('listener.xml');
        $loader->load('eraser.xml');
        $loader->load('metadata.xml');
        $loader->load('forms.xml');
        $loader->load('process_control.xml');
        $loader->load('validator.xml');

        if ($config['register_default_jobs']) {
            $this->registerDefaultJobs($container, $loader);
        }

        $this->loadLogger($config['logging'], $container, $loader, $config['db_driver']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param array            $services
     * @return void
     */
    protected function configureServices(ContainerBuilder $container, array $config, array $services)
    {
        foreach ($services as $name) {
            $container->setAlias('abc.job.'. $name, $config['service'][$name]);
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $map
     * @return void
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
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
    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
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

    /**
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     * @return void
     */
    private function registerDefaultJobs(ContainerBuilder $container, XmlFileLoader $loader) {
        $container->setParameter('abc.job.form.form_type_message', method_exists(AbstractType::class, 'getBlockPrefix') ? MessageType::class : 'abc_job_message');
        $container->setParameter('abc.job.form.form_type_seconds', method_exists(AbstractType::class, 'getBlockPrefix') ? SecondsType::class : 'abc_job_seconds');

        $loader->load('default_jobs.xml');

        if(!method_exists(AbstractType::class, 'getBlockPrefix')){
            $loader->load('default_jobs_forms.xml');
        }
    }

    private function loadLogger(array $config, ContainerBuilder $container, XmlFileLoader $loader, $dbDriver)
    {
        if ('custom' !== $config['handler']) {
            $loader->load('logger_' . $config['handler'] . '.xml');

            if ('orm' == $config['handler']) {
                $container->setParameter('abc.job.register_mapping.' . $dbDriver, true);
            }
        }

        if (isset($config['formatter'])) {
            $jobType = $container->getDefinition('abc.job.logger.factory');
            $jobType->addMethodCall('setFormatter', array(new Reference($config['formatter'])));
        }

        if (!empty($config['processor'])) {
            $jobType = $container->getDefinition('abc.job.logger.factory');

            foreach ($config['processor'] as $serviceId) {
                $jobType->addMethodCall('addProcessor', array(new Reference($serviceId)));
            }
        }

        $container->setParameter('abc.job.logging.default_level', $config['default_level']);
        $container->setParameter('abc.job.logging.custom_level', $config['custom_level']);
    }
}