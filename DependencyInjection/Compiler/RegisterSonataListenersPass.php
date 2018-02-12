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

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the service abc.job.producer as listener for all messages dispatched from the sonata backend.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RegisterSonataListenersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $queueEngineService;

    /**
     * @var string
     */
    protected $dispatcherService;

    /**
     * @var string
     */
    private $jobTag;

    /**
     * @param string $dispatcherService Service name of the sonata event dispatcher in processed container
     * @param string $queueEngineService
     * @param string $jobTag
     */
    public function __construct($dispatcherService = 'sonata.notification.dispatcher', $queueEngineService = 'abc.job.producer', $jobTag = 'abc.job')
    {
        $this->dispatcherService  = $dispatcherService;
        $this->queueEngineService = $queueEngineService;
        $this->jobTag             = $jobTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if(!($container->hasDefinition($this->queueEngineService) || !$container->hasAlias($this->queueEngineService))
            && !($container->hasDefinition($this->dispatcherService) || $container->hasAlias($this->dispatcherService))
        )
        {
            return;
        }

        $dispatcher = $container->getDefinition($this->dispatcherService);

        foreach($container->findTaggedServiceIds($this->jobTag) as $id => $tags)
        {
            foreach($tags as $tag)
            {
                // workaround
                $dispatcher->addMethodCall(
                    'addListener',
                    array(
                        $tag['type'],
                        array(new ServiceClosureArgument(new Reference($this->queueEngineService)), 'process')
                    )
                );
            }
        }
    }
}