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
use Sonata\NotificationBundle\Backend\QueueDispatcherInterface;
use Sonata\NotificationBundle\Event\IterateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConsumerAdapter implements ConsumerInterface
{
    /**
     * @var BackendInterface
     */
    private $backend;

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
     * @var string
     */
    private $defaultQueue;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param BackendInterface         $backend
     * @param EventDispatcherInterface $evendDispatcher
     * @param EventDispatcherInterface $notificationDispatcher
     * @param ControllerInterface      $controller
     * @param string                   $defaultQueue The name of the default queue
     */
    public function __construct(
        BackendInterface $backend,
        EventDispatcherInterface $evendDispatcher,
        EventDispatcherInterface $notificationDispatcher,
        ControllerInterface $controller,
        $defaultQueue
    )
    {
        $this->backend                = $backend;
        $this->eventDispatcher        = $evendDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->controller             = $controller;
        $this->defaultQueue           = $defaultQueue;
        $this->options                = ['max-iterations' => PHP_INT_MAX];
    }

    public function consume($queue, array $options = [])
    {
        $this->configure($options);

        $backend = $this->getBackend($queue);

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
     * Within SonataNotificationBundle multiple backends are created if multiple queues are defined where
     * each backend is registered with a certain $type key.
     *
     * @param string|null $queue
     * @return BackendInterface
     */
    protected function getBackend($queue = null)
    {
        if($queue == $this->defaultQueue) {
            $queue = null;
        }

        if ($queue != null) {
            if (!$this->backend instanceof QueueDispatcherInterface) {
                throw new \RuntimeException(sprintf('Unable to use the provided type %s with a non QueueDispatcherInterface backend', $queue));
            }

            return $this->backend->getBackend($queue);
        }

        return $this->backend;
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