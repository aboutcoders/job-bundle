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

use Abc\Bundle\JobBundle\Locker\NullLocker;
use Abc\Bundle\ResourceLockBundle\Model\LockInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LockerConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if($container->getParameter('abc.job.locker_service') == 'abc.job.locker.default')
        {
            if(!$container->hasParameter('abc.resource_lock.lock_manager.class'))
            {
                $container->removeDefinition('abc.job.locker.default');
                $container->setDefinition('abc.job.locker', new Definition(NullLocker::class));
            }
        }
        else {
            $class = $container->getParameterBag()->resolveValue(
                $container->findDefinition($container->getParameter('abc.job.locker_service'))->getClass()
            );

            if (!in_array(LockInterface::class, class_implements($class))) {
                throw new \InvalidArgumentException(sprintf('"abc.job.locker" must implement %s (instance of %s given).', LockInterface::class, $class));
            }
        }
    }
}