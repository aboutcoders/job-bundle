<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Report;

use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Job\LogManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Eraser implements EraserInterface
{
    /** @var JobManagerInterface */
    private $jobManager;

    /** @var LogManagerInterface */
    private $logManager;

    /** @var LoggerInterface */
    private $logger;


    /**
     * @param JobManagerInterface $jobManager
     * @param LogManagerInterface $logManager
     * @param LoggerInterface     $logger
     */
    function __construct(JobManagerInterface $jobManager, LogManagerInterface $logManager, LoggerInterface $logger = null)
    {
        $this->jobManager = $jobManager;
        $this->logManager = $logManager;
        $this->logger     = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseByTickets(array $tickets)
    {
        $this->deleteJobs($this->jobManager->findByTickets($tickets));
    }

    /**
     * {@inheritdoc}
     */
    public function eraseByTypes(array $types)
    {
        $this->deleteJobs($this->jobManager->findByTypes($types));
    }

    /**
     * {@inheritdoc}
     */
    public function eraseByAge($days, array $types = array())
    {
        $this->deleteJobs($this->jobManager->findByAgeAndTypes($days, !is_array($types) ? array() : $types));
    }

    /**
     * @param JobInterface $job
     * @return void
     */
    private function deleteJob(JobInterface $job)
    {
        $this->logger->debug('Delete job {ticket}', array('ticket' => $job->getTicket()));

        $this->deleteLog($job);
        $this->jobManager->delete($job);
    }

    /**
     * @param array $jobs
     * @return void
     */
    private function deleteJobs(array $jobs)
    {
        foreach($jobs as $job)
        {
            $this->deleteJob($job);
        }
    }

    /**
     * @param JobInterface $job
     */
    private function deleteLog(JobInterface $job)
    {
        try
        {
            $this->logManager->deleteByJob($job);
        }
        catch(\RuntimeException $e)
        {
            $this->logger->error('Deletion of log file of job {ticket} failed with the {exception}', array('ticket' => $job->getTicket(), 'exception' => $e));
        }
    }
}