<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\ProcessControl;

use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Job\ProcessControl\StatusController;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StatusControllerTest extends TestCase
{
    use PHPMock;

    /**
     * Namespace of the test subject
     */
    const TEST_SUBJECT_NAMESPACE = 'Abc\Bundle\JobBundle\Job\ProcessControl';

    /**
     * @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var JobInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $job;

    /**
     * @var integer
     */
    private static $interval = 100;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $time;

    /**
     * @var StatusController
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->manager = $this->createMock(JobManagerInterface::class);
        $this->job     = $this->createMock(JobInterface::class);
        $this->time    = $this->getFunctionMock(StatusControllerTest::TEST_SUBJECT_NAMESPACE, 'time');

        $this->manager->expects($this->any())
            ->method('getClass')
            ->willReturn(JobInterface::class);

        $this->subject = new StatusController($this->job, $this->manager, static::$interval);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructValidatesInterval()
    {
        new StatusController($this->job, $this->manager, -1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructValidatesJob()
    {
        $job = $this->createMock(\Abc\Bundle\JobBundle\Job\JobInterface::class);

        new StatusController($job, $this->manager, -1);
    }

    public function testDoStopRefreshesOnFirstInvocation()
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn(Status::CANCELLED());

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->subject->doStop();
    }

    /**
     * @param Status $status
     * @dataProvider provideCancelStatus
     */
    public function testDoStopReturnsTrueIfJobStatus(Status $status)
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->assertTrue($this->subject->doStop());
    }

    /**
     * @param Status $status
     * @dataProvider provideNonCancelStatus
     */
    public function testDoStopReturnsFalseIfJobStatusIs(Status $status)
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->assertFalse($this->subject->doStop());
    }

    /**
     * @param $secondsPassed
     * @dataProvider provideSecondsGreaterOrEqualToInterval
     */
    public function testDoStopRefreshesIfIntervalExceeded($secondsPassed)
    {
        $this->manager->expects($this->exactly(2))
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn(Status::CANCELLING());

        $this->time->expects($this->at(0))->willReturn(0);
        $this->time->expects($this->at(1))->willReturn($secondsPassed);

        // initial invocation
        $this->subject->doStop();

        // second invocation
        $this->subject->doStop();
    }

    /**
     * @param $secondsPassed
     * @dataProvider provideSecondsLessThanInterval
     */
    public function testDoStopSkipsRefreshingIfIntervalNotExceeded($secondsPassed)
    {
        $this->manager->expects($this->exactly(1))
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn(Status::CANCELLING());

        $this->time->expects($this->at(0))->willReturn(0);
        $this->time->expects($this->at(1))->willReturn($secondsPassed);

        // initial invocation
        $this->subject->doStop();

        // second invocation
        $this->subject->doStop();
    }

    public static function provideCancelStatus() {
        return [
            [Status::CANCELLED()],
            [Status::CANCELLING()],
        ];
    }

    public static function provideNonCancelStatus() {
        return [
            [Status::REQUESTED()],
            [Status::PROCESSING()],
            [Status::PROCESSED()],
            [Status::SLEEPING()],
            [Status::ERROR()]
        ];
    }

    public static function provideSecondsGreaterOrEqualToInterval()
    {
        return [
            [static::$interval],
            [101],
            [1000]
        ];
    }

    public static function provideSecondsLessThanInterval()
    {
        return [
            [99],
            [1],
            [0]
        ];
    }
}