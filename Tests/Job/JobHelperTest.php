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
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Logger\Factory\FactoryInterface;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerFactory;

    /**
     * @var JobHelper
     */
    private $subject;

    public function setUp()
    {
        $this->loggerFactory = $this->getMock(FactoryInterface::class);

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
     */
    public function testUpdateJobWithStatusTerminatedSetsTerminatedAt($status)
    {
        $dateTime = new \DateTime;
        $job      = new Job();

        $this->subject->updateJob($job, $status);
        $this->assertLessThanOrEqual($dateTime, $job->getTerminatedAt());
    }

    /**
     * @dataProvider getTerminatedStatus
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

    public function testCopyJob()
    {
        /**
         * @var ScheduleInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $schedule = $this->getMock(ScheduleInterface::class);

        $original = new Job();
        $original->setTicket('JobTicket');
        $original->setResponse('JobResponse');
        $original->setStatus(Status::REQUESTED());
        $original->addSchedule($schedule);

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