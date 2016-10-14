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
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\NotificationBundle\Backend\BackendInterface;
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
     * @var SerializationHelper
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param BackendProvider          $backendProvider
     * @param EventDispatcherInterface $dispatcher
     * @param JobTypeRegistry          $registry
     * @param SerializationHelper      $serializer
     * @param LoggerInterface          $logger
     */
    function __construct(
        BackendProvider $backendProvider,
        EventDispatcherInterface $dispatcher,
        JobTypeRegistry $registry,
        SerializationHelper $serializer,
        LoggerInterface $logger = null)
    {
        $this->backendProvider = $backendProvider;
        $this->dispatcher      = $dispatcher;
        $this->registry        = $registry;
        $this->serializer      = $serializer;
        $this->logger          = $logger == null ? new NullLogger() : $logger;
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
        $body = ['type' => $message->getType()];
        if (null != $message->getTicket()) {
            $body['ticket'] = $message->getTicket();
        }
        if (null != $message->getParameters()) {
            $body['parameters'] = $this->serializer->serializeParameters($message->getType(), $message->getParameters());
        }

        try {
            $this->logger->debug('Publish message sonata backend', ['message' => $message]);

            $queue = $this->registry->get($message->getType())->getQueue();

            $this->backendProvider->getBackend($queue)->createAndPublish($type, $body);

        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to publish message (Error: %s)', $e->getMessage()), ['exception' => $e]);

            if (!$e instanceof \RuntimeException) {
                $e = new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }
    }

    /**
     *  Forwards messages to the job manager.
     *
     * This method is registered as the event handler for messages from the sonata backend.
     *
     * @param ConsumerEvent $event
     * @throws \InvalidArgumentException If the message body does not contain the expected data
     */
    public function process(ConsumerEvent $event)
    {
        $message = new Message(
            $event->getMessage()->getValue('type'),
            $event->getMessage()->getValue('ticket', null)
        );

        if (null != $event->getMessage()->getValue('parameters', null)) {
            $message->setParameters($this->serializer->deserializeParameters(
                $event->getMessage()->getValue('type'),
                $event->getMessage()->getValue('parameters')
            ));
        }

        $this->logger->debug('Consume message from bernard backend', ['message' => $message]);

        $this->manager->handleMessage($message);
    }
}