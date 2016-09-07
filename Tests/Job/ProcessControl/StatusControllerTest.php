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

/**
 * @runTestsInSeparateProcesses
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StatusControllerTest extends \PHPUnit_Framework_TestCase
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
        $this->manager = $this->getMock(JobManagerInterface::class);
        $this->job     = $this->getMock(JobInterface::class);
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
        $job = $this->getMock(\Abc\Bundle\JobBundle\Job\JobInterface::class);

        new StatusController($job, $this->manager, -1);
    }

    public function testDoExitRefreshesOnFirstInvocation()
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn(Status::CANCELLED());

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->subject->doExit();
    }

    /**
     * @param Status $status
     * @dataProvider provideCancelStatus
     */
    public function testDoExitReturnsTrueIfJobStatus(Status $status)
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->assertTrue($this->subject->doExit());
    }

    /**
     * @param Status $status
     * @dataProvider provideNonCancelStatus
     */
    public function testDoExitReturnsFalseIfJobStatusIs(Status $status)
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->assertFalse($this->subject->doExit());
    }

    /**
     * @param $secondsPassed
     * @dataProvider provideSecondsGreaterOrEqualToInterval
     */
    public function testDoExitRefreshesIfIntervalExceeded($secondsPassed)
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
        $this->subject->doExit();

        // second invocation
        $this->subject->doExit();
    }

    /**
     * @param $secondsPassed
     * @dataProvider provideSecondsLessThanInterval
     */
    public function testDoExitSkipsRefreshingIfIntervalNotExceeded($secondsPassed)
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
        $this->subject->doExit();

        // second invocation
        $this->subject->doExit();
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