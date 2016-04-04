<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Report;

use Abc\Bundle\JobBundle\Job\LogManagerInterface;
use Abc\Bundle\JobBundle\Job\Report\Eraser;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class EraserTest extends \PHPUnit_Framework_TestCase
{

    /** @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $jobManager;
    /** @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logManager;
    /** @var Eraser */
    private $subject;

    public function setUp()
    {
        $this->jobManager    = $this->getMock('Abc\Bundle\JobBundle\Model\JobManagerInterface');
        $this->logManager    = $this->getMock('Abc\Bundle\JobBundle\Job\LogManagerInterface');

        $this->subject = new Eraser($this->jobManager, $this->logManager);
    }

    public function testEraseByTickets()
    {
        $tickets = array('foo', 'bar');
        $job     = new Job();
        $job->setTicket('ticket');
        $jobs = array($job);

        $this->jobManager->expects($this->once())
            ->method('findByTickets')
            ->with($tickets)
            ->willReturn($jobs);

        $this->expectJobDataIsDeleted($job);

        $this->subject->eraseByTickets($tickets);
    }

    public function testEraseByTypes()
    {
        $types = array('foo', 'bar');
        $job   = new Job();
        $job->setTicket('ticket');
        $jobs = array($job);

        $this->jobManager->expects($this->once())
            ->method('findByTypes')
            ->with($types)
            ->willReturn($jobs);

        $this->expectJobDataIsDeleted($job);

        $this->subject->eraseByTypes($types);
    }

    public function testEraseByAge()
    {
        $age   = 5;
        $types = array('foo', 'bar');
        $job   = new Job();
        $job->setTicket('ticket');
        $jobs = array($job);

        $this->jobManager->expects($this->once())
            ->method('findByAgeAndTypes')
            ->with($age, $types)
            ->willReturn($jobs);

        $this->expectJobDataIsDeleted($job);

        $this->subject->eraseByAge($age, $types);
    }

    public function testEraseHandlesLoggerFactoryExceptions()
    {
        $tickets = array('foo', 'bar');
        $job     = new Job();
        $job->setTicket('ticket');
        $jobs = array($job);

        $this->jobManager->expects($this->once())
            ->method('findByTickets')
            ->with($tickets)
            ->willReturn($jobs);

        $this->jobManager->expects($this->once())
            ->method('delete')
            ->with($job);

        $this->logManager->expects($this->once())
            ->method('deleteByJob')
            ->with($job)
            ->willThrowException(new \RuntimeException());

        $this->subject->eraseByTickets($tickets);
    }

    public function testEraseHandlesListenerExceptions()
    {
        $tickets = array('foo', 'bar');
        $job     = new Job();
        $job->setTicket('ticket');
        $jobs = array($job);

        $this->jobManager->expects($this->once())
            ->method('findByTickets')
            ->with($tickets)
            ->willReturn($jobs);

        $this->jobManager->expects($this->once())
            ->method('delete')
            ->with($job);

        $this->subject->eraseByTickets($tickets);
    }

    /**
     * @param JobInterface $job
     */
    private function expectJobDataIsDeleted(JobInterface $job)
    {
        $this->jobManager->expects($this->once())
            ->method('delete')
            ->with($job);

        $this->logManager->expects($this->once())
            ->method('deleteByJob')
            ->with($job);
    }
}