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

use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Bernard\Consumer;
use Bernard\QueueFactory;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConsumerAdapter implements ConsumerInterface
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var QueueFactory;
     */
    private $queueFactory;

    /**
     * @param Consumer $consumer
     * @param QueueFactory       $queueFactory
     */
    public function __construct(Consumer $consumer, QueueFactory $queueFactory)
    {
        $this->consumer     = $consumer;
        $this->queueFactory = $queueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function consume($queue, array $options = [])
    {
        $queue = $this->queueFactory->create($queue);

        $this->consumer->consume($queue, $options);
    }
}