<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface;

/**
 * ManagerInterface defines functionality to manage asynchronous processing of jobs.
 *
 * Implementations of this interface have to work with some queue engine.
 *
 * The actual processing of a job is triggered by a message that is passed to the method onMessage().
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ManagerInterface
{
    /**
     * Creates a job
     *
     * @param string                 $type
     * @param array|null             $parameters
     * @param ScheduleInterface|null $schedule
     * @return JobInterface
     */
    public function create($type, array $parameters = null, ScheduleInterface $schedule = null);

    /**
     * Adds a job for asynchronous processing.
     *
     * @param JobInterface $job
     * @return JobInterface The added job (not necessarily the same instance as the given one)
     */
    public function add(JobInterface $job);

    /**
     * Adds a job for asynchronous processing.
     *
     * @param string                 $type The job type
     * @param array|null             $parameters The job parameters
     * @param ScheduleInterface|null $schedule The schedule of the job
     * @return JobInterface The added job (not necessarily the same instance as the given one)
     */
    public function addJob($type, array $parameters = null, ScheduleInterface $schedule = null);

    /**
     * Cancels execution of a job.
     *
     * @param JobInterface $job
     * @return void
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function cancel(JobInterface $job);

    /**
     * Cancels execution of a job.
     *
     * @param string $ticket The ticket of the job
     * @return void
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function cancelJob($ticket);

    /**
     * Returns a job.
     *
     * @param string $ticket The job ticket
     * @return JobInterface
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function get($ticket);

    /**
     * Returns the logs of a job.
     *
     * @param JobInterface $job
     * @return string|null
     */
    public function getLogs(JobInterface $job);

    /**
     * Returns the logs of a job.
     *
     * @param string $ticket
     * @return string|null
     * @throws TicketNotFoundException
     */
    public function getJobLogs($ticket);

    /**
     * Handles a message from the queue engine.
     *
     * @param Message $message
     * @return void
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function onMessage(Message $message);
}