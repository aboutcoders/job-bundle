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
use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Queue;
use Bernard\Router;

/**
 * A custom implementation of Consumer that is controlled by a process controller and allows to set
 * maximum number of iterations
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
     * @var array
     */
    protected $options;

    /**
     * @var integer
     */
    private $iterations;
    
    /**
     * @param Router            $router
     * @param MiddlewareBuilder $middleware
     */
    public function __construct(Router $router, MiddlewareBuilder $middleware, ControllerInterface $controller)
    {
        parent::__construct($router, $middleware);

        $this->controller = $controller;
        $this->options = [
            'max-iterations' => PHP_INT_MAX,
            'exit-on-empty' => false
        ];
    }

    /**
     * Starts an infinite loop calling Consumer::tick();
     *
     * Following options can be set:
     *  * max-iterations: The maximum number of iterations
     *  * exit-on-empty: Whether to exit the loop when queue is empty
     *
     * @param Queue $queue
     * @param array $options
     */
    public function consume(Queue $queue, array $options = array())
    {
        $this->iterations = 0;

        while ($this->tick($queue, $options)) {
            // NO op
        }
    }

    /**
     * Returns true or false to indicate whether this method should be invoked again.
     *
     * @param  Queue $queue
     * @param  array $options
     * @return boolean Whether this method should be invoked again
     */
    public function tick(Queue $queue, array $options = array())
    {
        $this->configure($options);

        if ($this->controller->doExit()) {
            return false;
        }

        if ($this->iterations >= $this->options['max-iterations']) {
            return false;
        }

        if (!$envelope = $queue->dequeue()) {
            return !$this->options['exit-on-empty'];
        }

        $this->invoke($envelope, $queue);

        $this->iterations++;

        return true;
    }

    /**
     * @param array $options
     * @return void
     */
    protected function configure(array $options)
    {
        if ($this->configured) {
            return;
        }

        $this->options = array_merge($this->options, $options);
        $this->configured = true;
    }
}