<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Sonata;

use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\QueueEngineInterface;
use Psr\Log\LoggerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * QueueEngine adapter that works with a sonata backend.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 * @see https://sonata-project.org/bundles/notification/3-x/doc/index.html
 */
class SonataAdapter implements QueueEngineInterface, ConsumerInterface
{
    const MESSAGE_PREFIX = 'abc.job.';

    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param BackendInterface         $backend
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    function __construct(BackendInterface $backend, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->backend    = $backend;
        $this->dispatcher = $dispatcher;
        $this->logger     = $logger;
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
    public function publish(Message $message)
    {
        $type = self::MESSAGE_PREFIX . $message->getType();
        $body = array('ticket' => $message->getTicket());

        try
        {
            $this->logger->debug('Create and publish message of type {type} and body {body} to backend', array('type' => $type, 'body' => $body));

            $this->backend->createAndPublish($type, $body);
        }
        catch(\Exception $e)
        {
            $this->logger->error('Failed to publish message {exception}', array('exception' => $e));

            if(!$e instanceof \RuntimeException)
            {
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

        $ticket   = $event->getMessage()->getValue('ticket', null);
        $callback = $event->getMessage()->getValue('callback', null);

        if(!is_string($ticket) || strlen((string) $ticket) == 0)
        {
            throw new \InvalidArgumentException('The message body must be an array containing the key "ticket"');
        }

        $this->manager->onMessage(new Message($event->getMessage()->getType(), $ticket, $callback));
    }
}