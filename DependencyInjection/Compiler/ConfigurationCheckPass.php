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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Checks if dependent bundles are properly configured.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
final class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // DoctrineBundle
        if(!$container->has('doctrine')) {
            throw new \RuntimeException('You need to enable the DoctrineBundle');
        }

        // AbcEnumSerializerBundle
        if(!$container->has('abc.enum.jms_serializer.handler')) {
            throw new \RuntimeException('You need to enable the AbcEnumBundle');
        }

        // AbcSchedulerBundle
        if(!$container->has('abc.scheduler.scheduler')) {
            throw new \RuntimeException('You need to enable the AbcSchedulerBundle');
        }

        // if Rest API is enabled
        if ($container->hasParameter('abc.job.rest') && $container->getParameter('abc.job.rest')) {

            // SensioFrameworkExtraBundle
            if(!$container->has('sensio_framework_extra.converter.listener')) {
                throw new \RuntimeException('You need to enable the converters in SensioFrameworkExtraBundle when using AbcJobBundle REST API');
            }

            // FOSRestBundle
            if(!$container->has('fos_rest.serializer')) {
                throw new \RuntimeException('You need to enable the FOSRestBundle');
            }
        }
    }
}