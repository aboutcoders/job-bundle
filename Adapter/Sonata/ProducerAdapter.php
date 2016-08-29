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

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\ProducerInterface;
use Psr\Log\LoggerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Backend\QueueDispatcherInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * QueueEngine adapter that works with a sonata backend.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 * @see    https://sonata-project.org/bundles/notification/3-x/doc/index.html
 */
class ProducerAdapter implements ProducerInterface, ConsumerInterface
{
    /**
     * @var BackendInterface
     */
    protected $backendProvider;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param BackendProvider          $backendProvider
     * @param EventDispatcherInterface $dispatcher
     * @param JobTypeRegistry          $registry
     * @param LoggerInterface          $logger
     */
    function __construct(BackendProvider $backendProvider, EventDispatcherInterface $dispatcher, JobTypeRegistry $registry, LoggerInterface $logger)
    {
        $this->backendProvider = $backendProvider;
        $this->dispatcher      = $dispatcher;
        $this->registry        = $registry;
        $this->logger          = $logger;
    }

    /**
     * @param ManagerInterface $manager
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Publishes a message to the backend.
     *
     * @param Message $message
     * @return void
     * @throws \RuntimeException If publishing fails
     */
    public function produce(Message $message)
    {
        $type = $message->getType();
        $body = array('ticket' => $message->getTicket());

        try {
            $this->logger->debug('Publish message of type {type} and body {body} to sonata backend', array('type' => $type, 'body' => $body));

            $queue = $this->registry->get($message->getType())->getQueue();

            $this->backendProvider->getBackend($queue)->createAndPublish($type, $body);

        } catch (\Exception $e) {
            $this->logger->error('Failed to publish message {exception}', array('exception' => $e));

            if (!$e instanceof \RuntimeException) {
                $e = new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }
    }

    /**
     * @param ConsumerEvent $event
     * @throws \InvalidArgumentException If the message body does not contain the expected data
     */
    public function process(ConsumerEvent $event)
    {
        $this->logger->debug('Process event {event} from sonata backend', array('event' => $event));

        $ticket = $event->getMessage()->getValue('ticket', null);

        if (!is_string($ticket) || strlen((string)$ticket) == 0) {
            throw new \InvalidArgumentException('The message body must be an array containing the key "ticket"');
        }

        $this->manager->onMessage(new Message($event->getMessage()->getType(), $ticket));
    }
}