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
use Sonata\NotificationBundle\Model\MessageInterface;
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

        do {
            if ($iterations > 0) {
                usleep(500000);
            }

            $iterations++;
            $this->iterate($backend);
        } while (!$this->controller->doExit() && ($iterations < (int)$this->options['max-iterations']));
    }

    /**
     * @param BackendInterface $backend
     */
    protected function iterate(BackendInterface $backend)
    {
        $iterator = $backend->getIterator();
        foreach ($iterator as $message) {

            if (!$message instanceof MessageInterface) {
                throw new \RuntimeException('The iterator must return a MessageInterface instance');
            }

            if (!$message->getType()) {
                continue;
            }

            $backend->handle($message, $this->notificationDispatcher);

            $this->eventDispatcher->dispatch(IterateEvent::EVENT_NAME, new IterateEvent($iterator, $backend, $message));
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