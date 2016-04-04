<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Fixtures\Job;


use Abc\Bundle\JobBundle\Job\Job;
use Abc\Bundle\JobBundle\Job\JobAwareInterface;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\SchedulerBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Annotation\JobResponse;
use Psr\Log\LoggerInterface;

class TestCallable implements JobAwareInterface
{
    /** @var JobInterface */
    protected $job;

    public function setJob(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * @param                 $maxIterations
     * @param int             $iterations
     * @param LoggerInterface $logger
     * @JobParameters({"integer","integer", "@logger"})
     */
    public function schedule($maxIterations, $iterations = 0, LoggerInterface $logger)
    {
        $logger->info('schedule');

        $iterations++;

        $this->job->setParameters(array($maxIterations, $iterations));

        if($iterations >= $maxIterations)
        {
            $this->job->removeSchedules();

            $logger->info('removed schedule');
        }
    }

    /**
     * Creates a schedule
     *
     * @param string $type
     * @param string $expression
     * @JobParameters({"string","string"})
     */
    public function createSchedule($type, $expression)
    {
        $schedule = new Schedule();
        $schedule->setType($type);
        $schedule->setExpression($expression);

        $schedule = $this->job->createSchedule($type, $expression);
        $this->job->addSchedule($schedule);
    }

    /**
     * Removes a schedule
     *
     * @param LoggerInterface $logger
     * @JobParameters({"@logger"})
     */
    public function removeSchedule(LoggerInterface $logger)
    {
        $this->job->removeSchedules();

        $logger->info('removed schedule');
    }

    /**
     * Updates a schedule
     *
     * @param string $type
     * @param string $expression
     * @JobParameters({"string","string"})
     */
    public function updateSchedule($type, $expression)
    {
        $schedule = new Schedule();
        $schedule->setType($type);
        $schedule->setExpression($expression);

        $this->job->removeSchedules();
        $this->job->addSchedule($schedule);
    }

    /**
     * @param mixed $response
     * @return mixed $response
     * @JobParameters("Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse")
     * @JobResponse("Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse")
     */
    public function setResponse(TestResponse $response)
    {
        return $response;
    }

    /**
     * @param $message
     * @param $code
     * @throws \Exception
     * @JobParameters({"string","integer"})
     */
    public function throwException($message, $code)
    {
        throw new \Exception($message, $code);
    }

    /**
     * @param string          $message
     * @param LoggerInterface $logger
     * @JobParameters({"string", "@logger"})
     */
    public function log($message, LoggerInterface $logger)
    {
        $logger->debug($message);
        $logger->info($message);
        $logger->notice($message);
        $logger->warning($message);
        $logger->alert($message);
        $logger->critical($message);
    }
}