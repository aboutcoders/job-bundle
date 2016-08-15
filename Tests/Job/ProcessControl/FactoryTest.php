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
use Abc\Bundle\JobBundle\Job\ProcessControl\StatusController;
use Abc\Bundle\JobBundle\Job\ProcessControl\Factory;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\ProcessControl\ChainController;
use Abc\ProcessControl\ControllerInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $job;

    /**
     * @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var Factory
     */
    private $subject;

    public function setUp()
    {
        $this->manager  = $this->getMock(JobManagerInterface::class);
        $this->job      = $this->getMock(JobInterface::class);
        $this->interval = 250;

        $this->manager->expects($this->any())
            ->method('getClass')
            ->willReturn(JobInterface::class);

        $this->manager->expects($this->atLeastOnce())
            ->method('isManagerOf')
            ->with($this->job)
            ->willReturn(true);

        $this->subject = new Factory($this->manager, $this->interval);
    }

    public function testCreate()
    {
        $controller = $this->subject->create($this->job);

        $this->assertInstanceOf(JobController::class, $controller);
        $this->assertAttributeInstanceOf(StatusController::class, 'controller', $controller);
    }

    public function testCreateReturnsChainedController() {

        /**
         * @var ControllerInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $otherController = $this->getMock(ControllerInterface::class);

        $this->subject->addController($otherController);

        $controller = $this->subject->create($this->job);

        $this->assertInstanceOf(JobController::class, $controller);
        $this->assertAttributeInstanceOf(ChainController::class, 'controller', $controller);

        // assert that default controller is in the chain
        $this->manager->expects($this->once())
            ->method('refresh')
            ->with($this->job);

        // assert that additional controllers are in the chain
        $otherController->expects($this->once())
            ->method('doExit');

        $controller->doExit();
    }
}