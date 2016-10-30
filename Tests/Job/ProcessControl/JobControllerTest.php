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

use Abc\Bundle\JobBundle\Job\ProcessControl\JobController;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\ProcessControl\ControllerInterface;

class JobControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;

    /**
     * @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var Job|\PHPUnit_Framework_MockObject_MockObject
     */
    private $job;

    /**
     * @var JobController
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->controller = $this->createMock(ControllerInterface::class);
        $this->manager    = $this->createMock(JobManagerInterface::class);
        $this->job        = new Job();
        $this->job->setStatus(Status::REQUESTED());

        $this->manager->expects($this->any())
            ->method('isManagerOf')
            ->willReturn(true);

        $this->subject = new JobController($this->controller, $this->manager, $this->job);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructChecksIfJobIsManaged()
    {
        $manager = $this->createMock(JobManagerInterface::class);

        $manager->expects($this->once())
            ->method('isManagerOf')
            ->with($this->job)
            ->willReturn(false);

        new JobController($this->controller, $manager, $this->job);
    }

    public function testDoExitControllerReturnsFalse()
    {
        $this->controller->expects($this->any())
            ->method('doStop')
            ->willReturn(false);

        $this->assertFalse($this->subject->doStop());
        $this->assertEquals(Status::REQUESTED(), $this->job->getStatus());
    }

    public function testDoExitControllerReturnsTrue()
    {
        $this->controller->expects($this->any())
            ->method('doStop')
            ->willReturn(true);

        $this->assertTrue($this->subject->doStop());
        $this->assertEquals(Status::CANCELLED(), $this->job->getStatus());
    }

}
