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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Producer         $producer
     * @param JobTypeRegistry  $registry
     * @param LoggerInterface  $logger
     */
    public function __construct(Producer $producer, JobTypeRegistry $registry, LoggerInterface $logger = null)
    {
        $this->producer = $producer;
        $this->registry = $registry;
        $this->logger   = $logger == null ? new NullLogger() : $logger;
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
        $producerMessage = new DefaultMessage('ConsumeJob', [
            'type' => $message->getType(),
            'ticket' => $message->getTicket()
        ]);

        $this->logger->debug('Publish message to bernard queue backend', ['message' => $message]);

        $this->producer->produce($producerMessage, $this->registry->get($message->getType())->getQueue());
    }

    /**
     * Dispatches messages to the job manager.
     *
     * This method is registered as the message handler for messages with name "ConsumeJob".
     *
     * @param DefaultMessage $message
     */
    public function consumeJob(DefaultMessage $message){

        $ticket = $message->ticket;
        $type = $message->type;

        $this->logger->debug('Consume message from bernard backend', [
            'message' => $message
        ]);

        $this->manager->onMessage(new Message($type, $ticket));
    }
}