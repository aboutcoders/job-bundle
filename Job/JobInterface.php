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
     * @param string $type
     * @param $expression
     * @return ScheduleInterface
     */
    public function createSchedule($type, $expression);

    /**
     * @return bool
     */
    public function hasSchedules();

    /**
     * @return ScheduleInterface[]
     */
    public function getSchedules();

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
     * @return mixed|null
     */
    public function getResponse();

    /**
     * @return double The processing time in microseconds
     */
    public function getProcessingTime();

    /**
     * @return int The execution time from request creation to termination in seconds
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