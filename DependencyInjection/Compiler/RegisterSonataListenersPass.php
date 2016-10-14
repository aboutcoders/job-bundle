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
 * Registers the service abc.job.producer as listener for all messages dispatched from the sonata backend.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RegisterSonataListenersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $producerService;

    /**
     * @var string
     */
    protected $dispatcherService;

    /**
     * @var string
     */
    private $jobTag;

    /**
     * @param string $dispatcherService
     * @param string $producerService
     * @param string $jobTag
     */
    public function __construct($dispatcherService = 'sonata.notification.dispatcher', $producerService = 'abc.job.producer', $jobTag = 'abc.job')
    {
        $this->dispatcherService = $dispatcherService;
        $this->producerService   = $producerService;
        $this->jobTag            = $jobTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!($container->hasDefinition($this->producerService) || !$container->hasAlias($this->producerService))
            && !($container->hasDefinition($this->dispatcherService) || $container->hasAlias($this->dispatcherService))
        ) {
            return;
        }

        $dispatcher = $container->getDefinition($this->dispatcherService);
        foreach ($container->findTaggedServiceIds($this->jobTag) as $id => $tags) {
            foreach ($tags as $tag) {
                $dispatcher->addMethodCall(
                    'addListenerService',
                    array(
                        $tag['type'],
                        array($this->producerService, 'process')
                    )
                );
            }
        }
    }
}