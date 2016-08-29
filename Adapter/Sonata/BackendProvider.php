<?php
/*
* This file is part of the wcm-backend package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Adapter\Sonata;

use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Backend\MessageManagerBackendDispatcher;
use Sonata\NotificationBundle\Backend\QueueDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class BackendProvider
{
    /**
     * @var string
     */
    private $defaultQueue;

    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * @param BackendInterface $backend
     * @param string           $defaultQueue
     */
    public function __construct(BackendInterface $backend, $defaultQueue)
    {
        $this->defaultQueue = $defaultQueue;
        $this->backend      = $backend;
    }

    /**
     * @param string $queue
     * @return BackendInterface
     */
    public function getBackend($queue)
    {
        $type = null;

        if ($queue != $this->defaultQueue) {

            if (!$this->backend instanceof QueueDispatcherInterface) {
                throw new \RuntimeException(sprintf('Unable to provide sonata backend for queue %s', $queue));
            }

            if ($this->backend instanceof MessageManagerBackendDispatcher) {

                $queueConfig = $this->getQueueConfig($this->backend, $queue);
                if (!isset($queueConfig['types']) || !is_array($queueConfig['types']) || empty($queueConfig['types'])) {
                    throw new \RuntimeException('Invalid sonata queue configuration');
                }

                $type = $queueConfig['types'][0];

            } elseif ($this->backend instanceof AMQPBackendDispatcher) {
                $queueConfig = $this->getQueueConfig($this->backend, $queue);
                if (!isset($queueConfig['routing_key']) || empty($queueConfig['routing_key'])) {
                    throw new \RuntimeException('Invalid sonata queue configuration');
                }

                $type = $queueConfig['routing_key'];

            } else {
                throw new \RuntimeException('Unknown backend ' . get_class($this->backend));
            }
        }

        if ($this->backend instanceof QueueDispatcherInterface) {
            return $this->backend->getBackend($type);
        }

        return $this->backend;
    }

    /**
     * @param QueueDispatcherInterface $queueDispatcher
     * @param string                   $queue
     * @return array The queue configuration
     * @return array The queue configuration
     */
    private function getQueueConfig(QueueDispatcherInterface $queueDispatcher, $queue)
    {
        foreach ($queueDispatcher as $queueConfig) {
            if ($queue == $queueConfig['queue']) {
                return $queueConfig;
            }
        }

        throw new \InvalidArgumentException(sprintf('The queue %s is not configured within the sonata configuration', $queue));
    }
}