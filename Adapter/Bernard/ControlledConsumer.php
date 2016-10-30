<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Adapter\Bernard;

use Abc\ProcessControl\ControllerInterface;
use Bernard\Consumer as BaseConsumer;
use Bernard\Queue;
use Bernard\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A custom implementation of Consumer that is controlled by a process controller.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ControlledConsumer extends BaseConsumer
{
    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * @param Router                   $router
     * @param EventDispatcherInterface $dispatcher
     * @param ControllerInterface      $controller
     */
    public function __construct(Router $router, EventDispatcherInterface $dispatcher, ControllerInterface $controller)
    {
        parent::__construct($router, $dispatcher);

        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function tick(Queue $queue, array $options = [])
    {
        // weired, no clue why this is necessary, but somehow configure is not invoked otherwise
        $this->doConfigure($options);

        if ($this->controller->doStop()) {
            return false;
        }

        if ($this->controller->doPause()) {
            return true;
        }

        return parent::tick($queue, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function doConfigure(array $options)
    {
        parent::configure($options);
    }
}