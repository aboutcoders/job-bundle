<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Listener;

use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\ProducerInterface;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\SchedulerBundle\Event\SchedulerEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Listens to scheduler events for jobs and sends messages to the queue engine
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleListener
{
    /**
     * @var ProducerInterface
     */
    private $queueEngine;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProducerInterface    $queueEngine
     * @param LoggerInterface|null $logger
     */
    function __construct(ProducerInterface $queueEngine, LoggerInterface $logger = null)
    {
        $this->queueEngine = $queueEngine;
        $this->logger      = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * @param SchedulerEvent $event
     */
    public function onSchedule(SchedulerEvent $event)
    {
        $schedule = $event->getSchedule();
        if ($schedule instanceof Schedule) {

            $this->logger->debug('Process schedule {schedule}', array('schedule' => $schedule));

            if ($job = $schedule->getJob()) {
                $message = new Message($job->getType(), $job->getTicket());

                $this->queueEngine->produce($message);

                return;
            }

            $this->logger->error('There is no job associated with this schedule {schedule}', array('schedule' => $schedule));
        }
    }
}