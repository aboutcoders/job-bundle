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

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\ProducerInterface;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ProducerAdapter implements ProducerInterface
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var SerializationHelper
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Producer            $producer
     * @param JobTypeRegistry     $registry
     * @param SerializationHelper $serializer
     * @param LoggerInterface     $logger
     */
    public function __construct(Producer $producer, JobTypeRegistry $registry, SerializationHelper $serializer, LoggerInterface $logger = null)
    {
        $this->producer   = $producer;
        $this->registry   = $registry;
        $this->serializer = $serializer;
        $this->logger     = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function produce(Message $message)
    {
        $arguments = ['type' => $message->getType()];
        if ($message->getTicket() != null) {
            $arguments['ticket'] = $message->getTicket();
        }

        if ($message->getParameters() != null) {
            $arguments['parameters'] = $this->serializer->serializeParameters($message->getType(), $message->getParameters());
        }

        $producerMessage = new DefaultMessage('ConsumeJob', $arguments);

        $this->logger->debug('Publish message to bernard backend', ['message' => $message]);

        $this->producer->produce($producerMessage, $this->registry->get($message->getType())->getQueue());
    }

    /**
     * Forwards messages to the job manager.
     *
     * This method is registered as the message handler for messages with name "ConsumeJob".
     *
     * @param DefaultMessage $message
     */
    public function consumeJob(DefaultMessage $message)
    {
        $msg = new Message($message->type, $message->ticket);

        if (null != $message->parameters) {
            $msg->setParameters($this->serializer->deserializeParameters($message->type, $message->parameters));
        }

        $this->logger->debug('Consume message from bernard backend', ['message' => $message]);

        $this->manager->handleMessage($msg);
    }
}