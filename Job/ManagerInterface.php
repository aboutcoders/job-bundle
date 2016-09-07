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
     * @return JobInterface The added job
     */
    public function add(JobInterface $job);

    /**
     * Adds a job for asynchronous processing.
     *
     * @param string                 $type       The job type
     * @param array|null             $parameters The job parameters
     * @param ScheduleInterface|null $schedule   The schedule of the job
     * @return JobInterface
     */
    public function addJob($type, array $parameters = null, ScheduleInterface $schedule = null);

    /**
     * Cancels execution of a job.
     *
     * @param string  $ticket The ticket of the job
     * @param boolean $force  Whether to enforce cancellation
     * @return JobInterface|null The cancelled job, or null if given job is already terminated
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function cancel($ticket, $force = false);

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
     * @param string $ticket
     * @return array An array of Monolog compliant log records
     * @throws TicketNotFoundException
     */
    public function getLogs($ticket);

    /**
     * Handles a message from the queue engine.
     *
     * @param Message $message
     * @return void
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function onMessage(Message $message);

    /**
     * Restarts a job.
     *
     * @param string $ticket
     * @return JobInterface The restarted job
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function restart($ticket);

    /**
     * Updates a job.
     *
     * @param JobInterface $job
     * @return JobInterface The updated job
     * @throws TicketNotFoundException
     * @throws \RuntimeException
     */
    public function update(JobInterface $job);
}