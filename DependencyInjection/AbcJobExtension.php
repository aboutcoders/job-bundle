<?php

namespace Abc\JobBundle\DependencyInjection;

use Abc\Job\Broker\Config;
use Abc\Job\Interop\DriverFactory;
use Abc\Job\Interop\DriverInterface;
use Abc\Job\Job;
use Abc\Job\Symfony\DiUtils;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class AbcJobExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));

        $loader->load('services.yml');

        $diUtils = DiUtils::create();

        $bundles = $container->getParameter('kernel.bundles');
        if (! isset($bundles['EnqueueBundle'])) {
            throw new \LogicException('The "enqueue/enqueue-bundle" package has to be installed.');
        }

        $configId = $diUtils->format('config');
        $container->register($configId, Config::class)->setArguments([
            $config['prefix'],
            $config['separator'],
            $config['default_queue'],
            $config['default_replyTo'],
        ]);

        $container->setParameter($diUtils->parameter('default_queue'), $config['default_queue']);
        $container->setParameter($diUtils->parameter('default_replyTo'), $config['default_replyTo']);

        $driverFactoryId = $diUtils->format('driver_factory');
        $container->register($driverFactoryId, DriverFactory::class)->addArgument(new Reference($configId))->addArgument(new Reference($diUtils->format('route_collection')))->addArgument(new Reference('logger'));

        $driverId = $diUtils->format('driver');
        $container->register($driverId, DriverInterface::class)->setFactory([
            new Reference($driverFactoryId),
            'create',
        ])->addArgument(new Reference(sprintf('enqueue.transport.%s.context', $config['transport'])));

        // scheduler
        if (false == empty($config['scheduler']['enabled'])) {
            $loader->load('scheduler.yml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->registerDoctrineEntityMapping($container);
    }

    private function registerDoctrineEntityMapping(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (! isset($bundles['DoctrineBundle'])) {
            throw new \LogicException('The "doctrine/doctrine-bundle" package has to be installed.');
        }

        foreach ($container->getExtensionConfig('doctrine') as $config) {
            if (! empty($config['dbal'])) {
                $rc = new \ReflectionClass(Job::class);
                $rootDir = dirname($rc->getFileName());
                $container->prependExtensionConfig('doctrine', [
                    'orm' => [
                        'mappings' => [
                            'abc_job' => [
                                'is_bundle' => false,
                                'type' => 'xml',
                                'dir' => $rootDir.'/Doctrine/mapping',
                                'prefix' => 'Abc\Job\Model',
                            ],
                        ],
                    ],
                ]);
                break;
            }
        }
    }
}
