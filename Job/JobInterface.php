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

use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface;

/**
 * JobInterface defines public API of a job.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface JobInterface
{
    /**
     * @return string|null
     */
    public function getTicket();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return Status
     */
    public function getStatus();

    /**
     * @return array|null
     */
    public function getParameters();

    /**
     * @param array|null $parameters The serialized parameters
     * @return void
     */
    public function setParameters($parameters = null);

    /**
     * @return bool
     */
    public function hasSchedules();

    /**
     * @param ScheduleInterface $schedule
     * @return void
     */
    public function addSchedule(ScheduleInterface $schedule);

    /**
     * @param ScheduleInterface $schedule
     * @return void
     */
    public function removeSchedule(ScheduleInterface $schedule);

    /**
     * @return void
     */
    public function removeSchedules();

    /**
     * @return ScheduleInterface[]
     */
    public function getSchedules();

    /**
     * @return mixed|null
     */
    public function getResponse();

    /**
     * @return int The processing time in milliseconds
     */
    public function getProcessingTime();

    /**
     * @return int The total execution time from creation to termination in seconds
     */
    public function getExecutionTime();

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @return \DateTime|null
     */
    public function getTerminatedAt();
}