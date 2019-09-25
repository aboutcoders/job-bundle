<?php

namespace Abc\JobBundle\DependencyInjection\Compiler;

use Abc\Job\Broker\Route;
use Abc\Job\Broker\RouteCollection;
use Abc\Job\JobProviderInterface;
use Abc\Job\Symfony\DiUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildJobProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $diUtils = DiUtils::create();

        $routeCollectionId = $diUtils->format('route_collection');
        $defaultQueue = $container->getParameter($diUtils->parameter('default_queue'));
        $defaultReplyTo = $container->getParameter($diUtils->parameter('default_replyTo'));

        $tag = 'abc.job.provider';
        $routeCollection = new RouteCollection([]);
        foreach ($container->findTaggedServiceIds($tag) as $serviceId => $tagAttributes) {
            $providerDefinition = $container->getDefinition($serviceId);
            $providerClass = $providerDefinition->getClass();
            if (false == class_exists($providerClass)) {
                throw new \LogicException(sprintf('The provider class "%s" could not be found.', $providerClass));
            }

            if (false == is_subclass_of($providerClass, JobProviderInterface::class)) {
                throw new \LogicException(sprintf('A provider must implement "%s" interface to be used with the tag "%s"', JobProviderInterface::class, $tag));
            }

            /** @var JobProviderInterface $providerClass */
            $jobs = $providerClass::getJobs();

            if (empty($jobs)) {
                throw new \LogicException('Job provider must return something.');
            }

            if (is_string($jobs)) {
                $jobs = [$jobs];
            }

            if (! is_array($jobs)) {
                throw new \LogicException('Job provider configuration is invalid. Should be an array or string.');
            }

            foreach ($jobs as $key => $params) {
                if (is_string($params)) {
                    $routeCollection->add(new Route($params, $defaultQueue, $defaultReplyTo));
                } elseif (is_array($params)) {
                    $jobName = $params['name'] ?? null;
                    $queueName = $params['queue'] ?? $defaultQueue;
                    $replyTo = $params['replyTo'] ?? $defaultReplyTo;
                    $routeCollection->add(new Route($jobName, $queueName, $replyTo));
                } else {
                    throw new \LogicException(sprintf('Job provider configuration is invalid for "%s::getJobs()". Got "%s"', $providerClass, json_encode($providerClass::getJobs())));
                }
            }
        }

        $rawRoutes = $routeCollection->toArray();

        $routeCollectionService = $container->getDefinition($routeCollectionId);
        $routeCollectionService->replaceArgument(0, array_merge($routeCollectionService->getArgument(0), $rawRoutes));
    }
}
