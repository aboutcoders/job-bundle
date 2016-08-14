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
use Abc\Bundle\JobBundle\Job\ProcessControl\Controller;
use phpmock\phpunit\PHPMock;

/**
 * @runTestsInSeparateProcesses
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
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
     * @var Controller
     */
    private $subject;

    public function setUp()
    {
        $this->manager = $this->getMock(JobManagerInterface::class);
        $this->job     = $this->getMock(JobInterface::class);
        $this->time    = $this->getFunctionMock(ControllerTest::TEST_SUBJECT_NAMESPACE, 'time');

        $this->manager->expects($this->any())
            ->method('getClass')
            ->willReturn(JobInterface::class);

        $this->subject = new Controller($this->job, $this->manager, static::$interval);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructValidatesInterval()
    {
        new Controller($this->job, $this->manager, -1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructValidatesJob()
    {
        $job = $this->getMock(\Abc\Bundle\JobBundle\Job\JobInterface::class);

        new Controller($job, $this->manager, -1);
    }

    public function testDoExitRefreshesOnFirstInvocation()
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::CANCELLED());

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->subject->doExit();
    }

    public function testDoExitReturnsTrueIfJobIsCancelled()
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::CANCELLED());

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->never());

        $this->assertTrue($this->subject->doExit());
    }

    public function testDoExitReturnsFalseIfJobIsNotCancelled()
    {
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        $this->job->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::PROCESSING());

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

        $this->job->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(Status::CANCELLED());

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

        $this->job->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(Status::CANCELLED());

        $this->time->expects($this->at(0))->willReturn(0);
        $this->time->expects($this->at(1))->willReturn($secondsPassed);

        // initial invocation
        $this->subject->doExit();

        // second invocation
        $this->subject->doExit();
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