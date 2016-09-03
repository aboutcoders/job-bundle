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

use Abc\ProcessControl\ControllerInterface;
use Abc\ProcessControl\NullController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ControllerConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $controllerService = $container->getParameter('abc.job.controller_service');

        if ($controllerService != 'abc.process_control.controller') {

            $class = $container->getParameterBag()->resolveValue(
                $container->findDefinition($controllerService)->getClass()
            );

            if (!in_array(ControllerInterface::class, class_implements($class))) {
                throw new \InvalidArgumentException(sprintf('"abc.job.controller" must implement %s (instance of %s given).', ControllerInterface::class, $class));
            }
        } elseif ($container->hasDefinition('abc.process_control.controller') || $container->hasAlias('abc.process_control.controller')) {
            $container->setAlias('abc.job.controller', 'abc.process_control.controller');
        }
        else {
            $container->setDefinition('abc.job.controller', new Definition(NullController::class));
        }
    }
}