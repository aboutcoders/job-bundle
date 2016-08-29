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
use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Backend\MessageManagerBackendDispatcher;
use Sonata\NotificationBundle\Backend\QueueDispatcherInterface;
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
    protected $options;

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
        $this->options                = ['max-iterations' => PHP_INT_MAX];
    }

    public function consume($queue, array $options = [])
    {
        $this->configure($options);

        $backend = $this->backendProvider->getBackend($queue);

        $backend->initialize();

        $iterations = 0;
        $iterator   = $backend->getIterator();
        foreach ($iterator as $message) {

            if ($this->controller->doExit()) {
                return;
            }

            if ($iterations >= $this->options['max-iterations']) {
                return;
            }

            if (!$message->getType()) {
                continue;
            }

            $backend->handle($message, $this->notificationDispatcher);

            $this->eventDispatcher->dispatch(IterateEvent::EVENT_NAME, new IterateEvent($iterator, $backend, $message));

            $iterations++;
        }
    }

    /**
     * @param array $options
     * @return void
     */
    protected function configure(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }
}