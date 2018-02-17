<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job;

use Abc\Bundle\JobBundle\Job\JobHelper;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Logger\LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface;
use Abc\Bundle\SchedulerBundle\Schedule\SchedulerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobHelperTest extends TestCase
{
    /**
     * @var LoggerFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerFactory;

    /**
     * @var JobHelper
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->loggerFactory = $this->createMock(LoggerFactoryInterface::class);

        $this->subject = new JobHelper($this->loggerFactory);
    }

    public function testGetJobLogger()
    {
        $job = new Job();

        $logger = new NullLogger();

        $this->loggerFactory->expects($this->once())
            ->method('create')
            ->with($job)
            ->willReturn($logger);

        $this->assertSame($logger, $this->subject->getJobLogger($job));
    }

    /**
     * @dataProvider getNonTerminatedStatus
     * @param $status
     */
    public function testUpdateJobWithNonTerminatedStatusValues($status)
    {
        $schedule = new Schedule();
        $schedule->setIsActive(true);

        $job = new Job();
        $job->addSchedule($schedule);

        $this->subject->updateJob($job, $status);

        $this->assertEquals($status, $job->getStatus());
        $this->assertNull($job->getTerminatedAt());
        $this->assertTrue($schedule->getIsActive());
    }

    /**
     * @dataProvider getTerminatedStatus
     * @param $status
     * @throws \Exception
     */
    public function testUpdateJobWithStatusTerminatedSetsTerminatedAt($status)
    {
        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('PT10S'));
        $job = new Job();

        $this->subject->updateJob($job, $status);
        $this->assertLessThanOrEqual($dateTime, $job->getTerminatedAt());
    }

    /**
     * @dataProvider getTerminatedStatus
     * @param $status
     */
    public function testUpdateJobWithStatusTerminatedDisablesSchedule($status)
    {
        $schedule = new Schedule();

        $job = new Job();
        $job->addSchedule($schedule);

        $this->subject->updateJob($job, $status);

        $this->assertFalse($schedule->getIsActive());
    }

    public function testUpdateJobAddsProcessingTime()
    {
        $previousProcessingTime = (double)0.5;
        $job                    = new Job();
        $job->setProcessingTime($previousProcessingTime);

        $this->subject->updateJob($job, Status::PROCESSED(), (double)0.5);

        $this->assertEquals((double)1.0, $job->getProcessingTime());
    }

    public function testUpdateJobWithProcessingTimeIsNull()
    {
        $previousProcessingTime = (double)0.5;

        $job = new Job();
        $job->setProcessingTime($previousProcessingTime);

        $this->subject->updateJob($job, Status::PROCESSED());

        $this->assertEquals($previousProcessingTime, $job->getProcessingTime());
    }

    public function testUpdateJobSetsResponse()
    {
        $job = new Job();

        $this->subject->updateJob($job, Status::PROCESSED(), 0, ['response']);

        $this->assertEquals(['response'], $job->getResponse());
    }

    /**
     * @param JobInterface $original
     * @dataProvider provideCopyJobs
     */
    public function testCopyJob(JobInterface $original)
    {
        $copy = new Job();

        $returnValue = $this->subject->copyJob($original, $copy);

        $this->assertSame($copy, $returnValue);
        $this->assertNull($copy->getTicket());
        $this->assertEquals($original->getType(), $copy->getType());
        $this->assertEquals($original->getResponse(), $copy->getResponse());
        $this->assertEquals($original->getStatus(), $copy->getStatus());
        $this->assertEquals($original->getSchedules(), $copy->getSchedules());
    }

    /**
     * @return array
     */
    public function provideCopyJobs()
    {
        return [
            [$this->createJob('JobType')],
            [$this->createJob('JobType', Status::CANCELLED())],
            [$this->createJob('JobType', Status::CANCELLED(), array('foobar'))],
            [$this->createJob('JobType', Status::CANCELLED(), array('foobar'), $this->createMock(ScheduleInterface::class))],
            [$this->createJob('JobType', Status::CANCELLED(), array('foobar'), $this->createMock(ScheduleInterface::class), 'response')],
        ];
    }

    /**
     * @param string             $type
     * @param Status             $status
     * @param array              $parameters
     * @param SchedulerInterface $schedule
     * @param mixed              $response
     * @return Job
     */
    public function createJob($type, $status = null, $parameters = null, $schedule = null, $response = null)
    {

        $job = new Job();
        $job->setType($type);
        $job->setParameters($parameters);
        $job->setResponse('JobResponse');
        if ($status != null) {
            $job->setStatus($status);
        }
        if ($schedule !== null) {
            $job->addSchedule($schedule);
        }

        return $job;
    }

    /**
     * @return array
     */
    public static function getTerminatedStatus()
    {
        return [
            [Status::PROCESSED()],
            [Status::ERROR()],
            [Status::CANCELLED()],
        ];
    }

    /**
     * @return array
     */
    public static function getNonTerminatedStatus()
    {
        return [
            [Status::REQUESTED()],
            [Status::SLEEPING()],
            [Status::PROCESSING()],
        ];
    }
}