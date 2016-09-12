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

use Abc\Bundle\JobBundle\Logger\LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Model\JobInterface as EntityJobInterface;
use Psr\Log\LoggerInterface;

/**
 * Helper class to manage jobs.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobHelper
{
    /**
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * @param LoggerFactoryInterface $loggerFactory
     */
    function __construct(LoggerFactoryInterface $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * Updates a job.
     *
     * If the given status is ERROR or CANCELLED the child jobs of the given job will be terminated with status CANCELLED.
     *
     * @param EntityJobInterface $job
     * @param Status             $status
     * @param int                $processingTime
     * @param mixed|null         $response
     */
    public function updateJob(EntityJobInterface $job, Status $status, $processingTime = 0, $response = null)
    {
        $job->setStatus($status);
        $job->setProcessingTime($job->getProcessingTime() + ($processingTime === null ? 0 : $processingTime));
        $job->setResponse($response);

        if (Status::isTerminated($status)) {
            $job->setTerminatedAt(new \DateTime());
        }

        if ($job->hasSchedules() && Status::isTerminated($status)) {
            foreach ($job->getSchedules() as $schedule) {
                if (method_exists($schedule, 'setIsActive')) {
                    $schedule->setIsActive(false);
                }
            }
        }
    }

    /**
     * Calculates the processing time
     *
     * @param float $executionStart The microtime when the execution was started
     * @return double The processing time
     */
    public function calculateProcessingTime($executionStart)
    {
        return (double)microtime(true) - $executionStart;
    }

    /**
     * @param EntityJobInterface $job
     * @return LoggerInterface
     */
    public function getJobLogger(EntityJobInterface $job)
    {
        return $this->loggerFactory->create($job);
    }

    /**
     * Copies properties of a job to another job
     *
     * @param JobInterface                             $from The job where properties are copied from
     * @param \Abc\Bundle\JobBundle\Model\JobInterface $to   The job where where properties are copied to
     * @return \Abc\Bundle\JobBundle\Model\JobInterface The copied job
     */
    public function copyJob(JobInterface $from, \Abc\Bundle\JobBundle\Model\JobInterface $to)
    {
        $to->setType($from->getType());
        $to->setResponse($from->getResponse());
        $to->setParameters($from->getParameters());

        if ($status = $from->getStatus()) {
            $to->setStatus($status);
        }

        foreach ($from->getSchedules() as $schedule) {
            $to->addSchedule($schedule);
        }

        return $to;
    }
}