<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Listener;

use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\ProducerInterface;
use Abc\Bundle\JobBundle\Listener\ScheduleListener;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\SchedulerBundle\Event\SchedulerEvent;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $producer;

    /**
     * @var ScheduleListener
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->producer = $this->getMock(ProducerInterface::class);
        $this->subject  = new ScheduleListener($this->producer);
    }


    public function testOnSchedule()
    {
        $job = new Job();
        $job->setType('type');
        $job->setTicket('ticket');

        $schedule = new Schedule();
        $schedule->setJob($job);

        $event = new SchedulerEvent($schedule);

        $self = $this;

        $this->producer->expects($this->once())
            ->method('produce')
            ->willReturnCallback(
                function (Message $message) use ($self)
                {
                    $this->assertEquals('ticket', $message->getTicket());
                    $this->assertEquals('type', $message->getType());
                }
            );

        $this->subject->onSchedule($event);
    }

    public function testOnScheduleWithoutJob()
    {
        $event = new SchedulerEvent(new Schedule());

        $this->producer->expects($this->never())
            ->method('produce');

        $this->subject->onSchedule($event);
    }
}