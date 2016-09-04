<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\DependencyInjection\Compiler;

use Gedmo\Timestampable\TimestampableListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RegisterDoctrineListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ('orm' == $container->getParameter('abc.job.db_driver')) {

            $connection = $container->getParameter('abc.job.connection');

            $listener = $container->register('gedmo.listener.timestampable', TimestampableListener::class);
            $listener->addMethodCall('setAnnotationReader', [new Reference('annotation_reader')]);
            $listener->addTag('doctrine.event_subscriber', ['connection' => $connection]);

            $em = $container->getDefinition(sprintf('doctrine.dbal.%s_connection.event_manager', $container->getParameter('abc.job.connection')));
            $em->addMethodCall('addEventSubscriber', array($listener));
        }
    }
}