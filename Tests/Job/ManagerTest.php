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

use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Event\JobEvents;
use Abc\Bundle\JobBundle\Event\TerminationEvent;
use Abc\Bundle\JobBundle\Job\Context\ContextInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Invoker;
use Abc\Bundle\JobBundle\Job\JobHelper;
use Abc\Bundle\JobBundle\Job\Logger\FactoryInterface as LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Job\LogManagerInterface;
use Abc\Bundle\JobBundle\Job\Manager;
use Abc\Bundle\JobBundle\Job\Queue\QueueEngineInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\ResourceLockBundle\Model\LockManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 * @author Wojciech Ciolko <wojciech.ciolko@aboutcoders.com>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;
    /** @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $jobManager;
    /** @var Invoker|\PHPUnit_Framework_MockObject_MockObject */
    protected $invoker;
    /** @var LoggerFactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerFactory;
    /** @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logManager;
    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dispatcher;
    /** @var JobHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;
    /** @var LockManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $locker;    
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;
    /** @var QueueEngineInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $queueEngine;

    /** @var Manager */
    private $subject;

    public function setUp()
    {
        $this->registry      = $this->getMockBuilder('Abc\Bundle\JobBundle\Job\JobTypeRegistry')->disableOriginalConstructor()->getMock();
        $this->jobManager    = $this->getMock('Abc\Bundle\JobBundle\Model\JobManagerInterface');
        $this->invoker       = $this->getMockBuilder('Abc\Bundle\JobBundle\Job\Invoker')->disableOriginalConstructor()->getMock();
        $this->loggerFactory = $this->getMock('Abc\Bundle\JobBundle\Job\Logger\FactoryInterface');
        $this->logManager    = $this->getMock('Abc\Bundle\JobBundle\Job\LogManagerInterface');
        $this->dispatcher    = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->helper        = $this->getMockBuilder('Abc\Bundle\JobBundle\Job\JobHelper')->disableOriginalConstructor()->getMock();
        $this->locker        = $this->getMock('Abc\Bundle\ResourceLockBundle\Model\LockManagerInterface');
        $this->logger        = $this->getMock('Psr\Log\LoggerInterface');
        $this->queueEngine   = $this->getMock('Abc\Bundle\JobBundle\Job\Queue\QueueEngineInterface');

        $this->jobManager->method('getClass')
            ->willReturn('Abc\Bundle\JobBundle\Model\Job');

        $this->subject = new Manager(
            $this->registry,
            $this->jobManager,
            $this->invoker,
            $this->loggerFactory,
            $this->logManager,
            $this->dispatcher,
            $this->helper,
            $this->locker,
            $this->logger
        );

        $this->subject->setQueueEngine($this->queueEngine);
    }

    /**
     * @param string     $type
     * @param array|null $parameters
     * @param null       $schedule
     * @dataProvider provideAddJobArguments
     */
    public function testAdd($type, array $parameters = null, $schedule = null)
    {
        $job = new Job();
        $job->setTicket('ticket');
        $job->setType($type);
        $job->setParameters($parameters);

        if(!is_null($schedule))
        {
            $job->addSchedule($schedule);
        }

        $this->registry->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with($job);

        $this->queueEngine->expects($schedule == null ? $this->once() : $this->never())
            ->method('publish')
            ->with($this->equalTo(new Message($job->getType(), $job->getTicket())));

        $addedJob = $this->subject->add($job);

        $this->assertEquals($job, $addedJob);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testJobThrowsExceptionIfTypeIsNotRegistered()
    {
        $job = new Job();
        $job->setType('foobar');

        $this->registry->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->subject->add($job);
    }

    /**
     * @expectedException \Exception
     */
    public function testAddRethrowsBackendExceptions()
    {
        $job = new Job();
        $job->setTicket('ticket');

        $this->registry->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->queueEngine->expects($this->once())
            ->method('publish')
            ->willThrowException(new \Exception);

        $this->subject->add($job);
    }

    public function testCancel()
    {
        $job = new Job();
        $job->setTicket('ticket');

        $this->cancelExpectations($job);

        $this->subject->cancel($job);
    }

    public function testCancelJob()
    {
        $job = new Job();
        $job->setTicket('ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($job);

        $this->cancelExpectations($job);

        $this->subject->cancelJob($job->getTicket());
    }

    public function testGet()
    {
        $job = new Job();
        $job->setTicket('ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($job);

        $this->assertSame($job, $this->subject->get($job->getTicket()));
    }

    /**
     * @expectedException \Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException
     */
    public function testGetThrowsTicketNotFoundException()
    {
        $this->jobManager->expects($this->any())
            ->method('findByTicket')
            ->willReturn(null);

        $this->subject->get('ticket');
    }

    public function testGetLogs()
    {
        $job = new Job();
        $job->setTicket('ticket');

        $this->logManager->expects($this->once())
            ->method('findByJob')
            ->with($job)
            ->willReturn('logs');

        $this->assertSame('logs', $this->subject->getLogs($job));
    }

    public function testOnMessageHandlesExecutionEventExceptions()
    {
        $job     = new Job();
        $message = new Message('type', 'ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->willThrowException(new \Exception);

        $this->invoker->expects($this->once())
            ->method('invoke');

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX.$job->getTicket());

        $this->subject->onMessage($message);
    }

    public function testOnMessageDispatchesExecutionEvents()
    {
        $job     = new Job();
        $message = new Message('type', 'ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(
                JobEvents::JOB_PRE_EXECUTE,
                $this->callback(
                    function (ExecutionEvent $event) use ($job)
                    {
                        return $job === $event->getJob();
                    }
                )
            );

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX.$job->getTicket());

        // set something in context to ensure that job is invoked before JOB_POST_EXECUTE is dispatched
        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with(
                $this->anything(),
                $this->callback(
                    function (ContextInterface $context)
                    {
                        $context->set('name', 'foobar');

                        return true;
                    }
                )
            );

        $this->dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(
                JobEvents::JOB_POST_EXECUTE,
                $this->callback(
                    function (ExecutionEvent $event) use ($job)
                    {
                        return $job === $event->getJob() && $event->getContext()->has('name') && 'foobar' == $event->getContext()->get('name');
                    }
                )
            );

        $this->subject->onMessage($message);
    }

    public function testOnMessageSetsStatusToProcessing()
    {
        $job     = new Job();
        $message = new Message('type', 'ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->jobManager->expects($this->at(1))
            ->method('save')
            ->with(
                $this->callback(
                    function ($arg) use ($job)
                    {
                        return $arg === $job && $job->getStatus() == Status::PROCESSING();
                    }
                )
            );

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX.$job->getTicket());
        
        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with(
                $this->callback(
                    function (\Abc\Bundle\JobBundle\Job\JobInterface $job)
                    {
                        return Status::PROCESSING() == $job->getStatus();
                    }
                )
            );

        $this->subject->onMessage($message);
    }

    public function testOnMessageInvokesJob()
    {
        $type       = 'JobType';
        $ticket     = 'JobTicket';
        $microTime  = microtime(true);
        $parameters = array('parameters');
        $response   = 'response';

        $job = new Job($type);
        $job->setTicket($ticket);
        $job->setParameters($parameters);

        $message = new Message($type, $ticket);

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX.$job->getTicket());
        
        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with($job, $this->isInstanceOf('Abc\Bundle\JobBundle\Job\Context\Context'))
            ->willReturn($response);

        $this->helper->expects($this->once())
            ->method('calculateProcessingTime')
            ->with($this->greaterThanOrEqual($microTime))
            ->willReturn('microtime');

        $this->expectsCallsUpdateJob($job, Status::PROCESSED(), 'microtime', $response);

        $this->jobManager->expects($this->at(2))
            ->method('save')
            ->with(
                $this->callback(
                    function (JobInterface $job)
                    {
                        return $job->getStatus() == Status::PROCESSED();
                    }
                )
            );

        $this->subject->onMessage($message);
    }

    public function testOnMessageWithScheduledJob()
    {
        $message = new Message('type', 'ticket');

        $job = new Job($message->getType());
        $job->setTicket($message->getTicket());
        $job->addSchedule(new Schedule());

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX.$job->getTicket());
        
        $this->expectsCallsUpdateJob($job, Status::SLEEPING());

        $this->expectEventNeverDispatched(JobEvents::JOB_TERMINATED);

        $this->subject->onMessage($message);
    }

    /**
     * @dataProvider provideExceptions
     * @param \Exception $exception
     * @param null       $logger
     */
    public function testOnMessageHandlesExceptionsThrownByJob(\Exception $exception, $logger = null)
    {
        $job       = new Job();
        $microTime = microtime(true);
        $message   = new Message('type', 'ticket');

        if($logger != null)
        {
            $this->dispatcher->expects($this->at(0))
                ->method('dispatch')
                ->willReturnCallback(
                    function ($eventName, ExecutionEvent $event) use ($logger)
                    {
                        $event->getContext()->set('logger', $logger);
                    }
                );
        }

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with($job, $this->isInstanceOf('Abc\Bundle\JobBundle\Job\Context\Context'))
            ->willThrowException($exception);

        $this->helper->expects($this->once())
            ->method('calculateProcessingTime')
            ->with($this->greaterThanOrEqual($microTime))
            ->willReturn('microtime');

        $this->expectsCallsUpdateJob($job, Status::ERROR(), 'microtime');

        $this->jobManager->expects($this->at(2))
            ->method('save')
            ->with(
                $this->callback(
                    function (JobInterface $job)
                    {
                        return
                            $job->getStatus() == Status::ERROR();
                    }
                )
            );

        $this->subject->onMessage($message);
    }

    /**
     * @expectedException \Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException
     */
    public function testOnMessageThrowsTicketNotFoundException()
    {
        $ticket  = 'ticketValue';
        $message = new Message('type', $ticket);

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($ticket)
            ->willReturn(null);

        $this->locker->expects($this->never())
            ->method('lock');

        $this->subject->onMessage($message);
    }

    /**
     * @param Status $status
     * @dataProvider provideStatusToSkip
     */
    public function testOnMessageSkipsExecutionIfStatusIs(Status $status)
    {
        $message = new Message('job-type', 'job-ticket');

        $job = new Job();
        $job->setType($message->getType());
        $job->setTicket($message->getTicket());
        $job->setStatus($status);

        $this->jobManager->expects($this->any())
            ->method('findByTicket')
            ->willReturn($job);

        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->locker->expects($this->never())
            ->method('lock');

        $this->subject->onMessage($message);
    }

    /**
     * @return array
     */
    public static function provideExceptions()
    {
        return [
            [new \Exception('foo', 100)],
            [new \Exception('foo', 100), new NullLogger()]
        ];
    }

    public static function provideStatusToSkip()
    {
        return [
            [Status::PROCESSING()],
            [Status::CANCELLED()]
        ];
    }

    /**
     * @return array
     */
    public function provideAddJobArguments()
    {
        return [
            ['job-type'],
            ['job-type', []],
            ['job-type', ['parameter']],
            ['job-type', ['parameter'], new Schedule()]
        ];
    }

    /**
     * Creates an expectation that $helper->updateJob is called.
     *
     * @param JobInterface $expectedJob The expected first argument passed to updateJob
     * @param Status       $status The expected second argument passed to updateJob
     * @param mixed|null   $processingTime The optional expected third argument passed to updateJob
     */
    protected function expectsCallsUpdateJob(JobInterface $expectedJob, Status $status, $processingTime = null, $response = null)
    {
        if(null == $response)
        {
            $this->helper->expects($this->once())
                ->method('updateJob')
                ->with($expectedJob, $this->equalTo($status), $processingTime)
                ->willReturnCallback(
                    function (JobInterface $job) use ($status, $processingTime)
                    {
                        $job->setStatus($status);
                        if($processingTime != null)
                        {
                            $job->setProcessingTime($processingTime);
                        }
                    }
                );
        }
        else
        {
            $this->helper->expects($this->once())
                ->method('updateJob')
                ->with($expectedJob, $this->equalTo($status), $processingTime, $response)
                ->willReturnCallback(
                    function (JobInterface $job) use ($status, $processingTime)
                    {
                        $job->setStatus($status);
                        if($processingTime != null)
                        {
                            $job->setProcessingTime($processingTime);
                        }
                    }
                );
        }

    }

    /**
     * Expects that an event with the given name is never dispatched
     *
     * @param $expectedEventName
     */
    protected function expectEventNeverDispatched($expectedEventName)
    {
        $this->dispatcher->expects($this->any())
            ->method('dispatch')
            ->with(
                $this->callback(
                    function ($name) use ($expectedEventName)
                    {
                        return $expectedEventName != JobEvents::JOB_TERMINATED;
                    }
                )
            );
    }

    /**
     * @param Job $job
     */
    protected function cancelExpectations(Job $job)
    {
        $terminationEvent = new TerminationEvent($job);

        $this->helper->expects($this->once())
            ->method('updateJob')
            ->with($job, Status::CANCELLED())
            ->willReturnCallback(
                function (JobInterface $job, Status $status) {
                    $job->setStatus($status);
                }
            );

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($arg) use ($job) {
                        return $arg === $job && $job->getStatus() == Status::CANCELLED();
                    }
                )
            );

        $this->locker->expects($this->once())
            ->method('release')
            ->with(Manager::JOB_LOCK_PREFIX.$job->getTicket());

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(JobEvents::JOB_TERMINATED, $terminationEvent);

    }
}