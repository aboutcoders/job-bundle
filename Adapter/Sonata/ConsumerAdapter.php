<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Adapter\Sonata;

use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Abc\ProcessControl\ControllerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Event\IterateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConsumerAdapter implements ConsumerInterface
{
    /**
     * @var BackendProvider
     */
    private $backendProvider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EventDispatcherInterface
     */
    private $notificationDispatcher;

    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * @var array
     */
    protected $options = [
        'max-runtime'     => PHP_INT_MAX,
        'max-messages'    => null,
        'stop-when-empty' => false,
        'stop-on-error'   => false,
    ];

    /**
     * @param BackendProvider          $backendProvider
     * @param EventDispatcherInterface $evendDispatcher
     * @param EventDispatcherInterface $notificationDispatcher
     * @param ControllerInterface      $controller
     */
    public function __construct(
        BackendProvider $backendProvider,
        EventDispatcherInterface $evendDispatcher,
        EventDispatcherInterface $notificationDispatcher,
        ControllerInterface $controller
    )
    {
        $this->backendProvider        = $backendProvider;
        $this->eventDispatcher        = $evendDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->controller             = $controller;
    }

    /**
     * Starts an infinite loop calling Consumer::tick();
     *
     * @param string $queue
     * @param array  $options
     */
    public function consume($queue, array $options = [])
    {
        $backend = $this->backendProvider->getBackend($queue);

        $backend->initialize();

        declare (ticks = 1);

        while ($this->tick($backend, $options)) {
            // NO op
        }
    }

    protected function tick(BackendInterface $backend, array $options = [])
    {
        $this->configure($options);

        $iterator = $backend->getIterator();

        foreach ($iterator as $message) {

            if ($this->controller->doPause()) {
                return true;
            }

            if ($this->controller->doStop()) {
                return false;
            }

            if (microtime(true) > $this->options['max-runtime']) {
                return false;
            }

            $backend->handle($message, $this->notificationDispatcher);

            $this->eventDispatcher->dispatch(IterateEvent::EVENT_NAME, new IterateEvent($iterator, $backend, $message));

            if (null !== $this->options['max-messages'] && !(boolean)--$this->options['max-messages']) {
                return false;
            }
        }

        return !$this->options['stop-when-empty'];
    }

    /**
     * @param array $options
     * @return void
     */
    protected function configure(array $options)
    {
        $this->options = array_filter($options) + $this->options;
        $this->options['max-runtime'] += microtime(true);
        $this->configured = true;
    }
}