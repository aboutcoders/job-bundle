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
use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\Context\ContextInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Invoker;
use Abc\Bundle\JobBundle\Job\JobHelper;
use Abc\Bundle\JobBundle\Job\LogManagerInterface;
use Abc\Bundle\JobBundle\Job\Manager;
use Abc\Bundle\JobBundle\Job\Queue\ProducerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Logger\LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Model\ScheduleInterface;
use Abc\Bundle\ResourceLockBundle\Exception\LockException;
use Abc\Bundle\ResourceLockBundle\Model\LockManagerInterface;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 * @author Wojciech Ciolko <wojciech.ciolko@aboutcoders.com>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jobManager;

    /**
     * @var Invoker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoker;

    /**
     * @var LoggerFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerFactory;

    /**
     * @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logManager;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var JobHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var LockManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locker;

    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $producer;

    /**
     * @var Manager
     */
    private $subject;

    public function setUp()
    {
        $this->registry      = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->jobManager    = $this->getMock(JobManagerInterface::class);
        $this->invoker       = $this->getMockBuilder(Invoker::class)->disableOriginalConstructor()->getMock();
        $this->loggerFactory = $this->getMock(LoggerFactoryInterface::class);
        $this->logManager    = $this->getMock(LogManagerInterface::class);
        $this->dispatcher    = $this->getMock(EventDispatcherInterface::class);
        $this->helper        = $this->getMockBuilder(JobHelper::class)->disableOriginalConstructor()->getMock();
        $this->locker        = $this->getMock(LockManagerInterface::class);
        $this->validator     = $this->getMock(ValidatorInterface::class);
        $this->logger        = $this->getMock(LoggerInterface::class);
        $this->producer      = $this->getMock(ProducerInterface::class);

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
            $this->validator,
            $this->logger
        );

        $this->subject->setProducer($this->producer);
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

        if (!is_null($schedule)) {
            $job->addSchedule($schedule);
        }

        $this->jobManager->expects($this->once())
            ->method('isManagerOf')
            ->with($job)
            ->willReturn(true);

        $this->registry->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with($job);

        $this->producer->expects($schedule == null ? $this->once() : $this->never())
            ->method('produce')
            ->with($this->equalTo(new Message($job->getType(), $job->getTicket())));

        $addedJob = $this->subject->add($job);

        $this->assertEquals($job, $addedJob);
    }

    public function testAddEnsuresJobIsManaged()
    {
        $job        = $this->getMock(JobInterface::class);
        $managedJob = $this->getMock(JobInterface::class);

        $this->registry->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->jobManager->expects($this->once())
            ->method('isManagerOf')
            ->with($job)
            ->willReturn(false);

        $this->jobManager->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($managedJob);

        $this->helper->expects($this->once())
            ->method('copyJob')
            ->with($job, $managedJob)
            ->willReturn($managedJob);

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with($job);

        $this->subject->add($job);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddThrowsExceptionIfTypeIsNotRegistered()
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

        $this->jobManager->expects($this->once())
            ->method('isManagerOf')
            ->with($job)
            ->willReturn(true);

        $this->registry->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->producer->expects($this->once())
            ->method('produce')
            ->willThrowException(new \Exception);

        $this->subject->add($job);
    }

    public function testAddJob()
    {
        $type       = 'JobType';
        $parameters = ['JobParameters'];
        $schedule   = $this->getMock(ScheduleInterface::class);
        $job        = $this->getMock(JobInterface::class);

        $subject = $this->createMockedSubject(['add']);

        $this->jobManager->expects($this->once())
            ->method('create')
            ->with($type, $parameters, $schedule)
            ->willReturn($job);

        $subject->expects($this->once())
            ->method('add')
            ->with($job);

        $subject->addJob($type, $parameters, $schedule);
    }

    /**
     * @param Status $status
     * @dataProvider provideUnterminatedStatus
     */
    public function testCancel(Status $status)
    {
        $isProcessing = $status->getValue() == Status::PROCESSING;

        $job = new Job();
        $job->setTicket('ticket');
        $job->setStatus($status);

        $terminationEvent = new TerminationEvent($job);

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($job);

        $this->helper->expects($this->once())
            ->method('updateJob')
            ->with($job, $isProcessing ? Status::CANCELLING() : Status::CANCELLED())
            ->willReturnCallback(
                function (JobInterface $job, Status $status) {
                    $job->setStatus($status);
                }
            );

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($arg) use ($job, $isProcessing) {
                        return $arg === $job && $arg->getStatus() == ($isProcessing ? Status::CANCELLING() : Status::CANCELLED());
                    }
                )
            );

        $this->dispatcher->expects($isProcessing ? $this->never() : $this->once())
            ->method('dispatch')
            ->with(JobEvents::JOB_TERMINATED, $terminationEvent);

        $this->subject->cancel($job->getTicket());
    }

    /**
     * @param Status $status
     * @dataProvider provideTerminatedStatus
     */
    public function testCancelWithTerminatedJob(Status $status)
    {
        $job = new Job();
        $job->setStatus($status);
        $job->setTicket('ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($job);

        $this->helper->expects($this->never())
            ->method('updateJob');

        $this->jobManager->expects($this->never())
            ->method('save');

        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertFalse($this->subject->cancel($job));
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

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($job);

        $this->logManager->expects($this->once())
            ->method('findByJob')
            ->with($job)
            ->willReturn('logs');

        $this->assertSame('logs', $this->subject->getLogs($job->getTicket()));
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
                    function (ExecutionEvent $event) use ($job) {
                        return $job === $event->getJob();
                    }
                )
            );

        // set something in context to ensure that job is invoked before JOB_POST_EXECUTE is dispatched
        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with(
                $this->anything(),
                $this->callback(
                    function (ContextInterface $context) {
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
                    function (ExecutionEvent $event) use ($job) {
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
                    function ($arg) use ($job) {
                        return $arg === $job && $job->getStatus() == Status::PROCESSING();
                    }
                )
            );

        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with(
                $this->callback(
                    function (\Abc\Bundle\JobBundle\Job\JobInterface $job) {
                        return Status::PROCESSING() == $job->getStatus();
                    }
                )
            );

        $this->subject->onMessage($message);
    }

    public function testOnMessageLockAndUnlockJob()
    {
        $job     = new Job();
        $message = new Message('type', 'ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX . $job->getTicket());

        $this->locker->expects($this->once())
            ->method('release')
            ->with(Manager::JOB_LOCK_PREFIX . $job->getTicket());
        $this->subject->onMessage($message);
    }

    public function testOnMessageSkipInvocationIfJobIsLocked()
    {
        $job     = new Job();
        $message = new Message('type', 'ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->locker->expects($this->once())
            ->method('lock')
            ->with(Manager::JOB_LOCK_PREFIX . $job->getTicket())
            ->willThrowException(new LockException());

        $this->invoker->expects($this->never())
            ->method('invoke');

        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->locker->expects($this->never())
            ->method('release');

        $this->subject->onMessage($message);
    }

    public function testOnMessageInvokesJob()
    {
        $type       = 'JobType';
        $ticket     = 'JobTicket';
        $microTime  = microtime(true);
        $parameters = ['parameters'];
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
            ->with(Manager::JOB_LOCK_PREFIX . $job->getTicket());

        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with($job, $this->isInstanceOf(Context::class))
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
                    function (JobInterface $job) {
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
            ->with(Manager::JOB_LOCK_PREFIX . $job->getTicket());

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

        if ($logger != null) {
            $this->dispatcher->expects($this->at(0))
                ->method('dispatch')
                ->willReturnCallback(
                    function ($eventName, ExecutionEvent $event) use ($logger) {
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
            ->with($job, $this->isInstanceOf(Context::class))
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
                    function (JobInterface $job) {
                        return
                            $job->getStatus() == Status::ERROR();
                    }
                )
            );

        $this->locker->expects($this->once())
            ->method('release')
            ->with(Manager::JOB_LOCK_PREFIX . $job->getTicket());

        $this->subject->onMessage($message);
    }

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     */
    public function testOnMessageHandlesDbalExceptionsThrownByJob()
    {
        $job       = new Job();
        $microTime = microtime(true);
        $message   = new Message('type', 'ticket');
        $exception = $this->getMockBuilder(DBALException::class)->disableOriginalConstructor()->getMock();
        $exception->expects($this->any())
            ->method('getMessage')
            ->willReturn('foobar');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->invoker->expects($this->once())
            ->method('invoke')
            ->with($job, $this->isInstanceOf(Context::class))
            ->willThrowException($exception);

        $this->subject->onMessage($message);
    }

    public function testOnMessageNotUpdatesStatusIfJobWasCancelled()
    {
        $job     = new Job();
        $message = new Message('type', 'ticket');

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($message->getTicket())
            ->willReturn($job);

        $this->invoker->expects($this->once())
            ->method('invoke')
            ->willReturnCallback(function (Job $job) {
                $job->setStatus(Status::CANCELLED());
            });

        $this->expectsCallsUpdateJob($job, Status::CANCELLED());

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

    public function testRestart()
    {
        $job = new Job();
        $job->setTicket('JobTicket');
        $job->setProcessingTime(500);

        $subject = $this->createMockedSubject(['add']);

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($job);

        $subject->expects($this->once())
            ->method('add')
            ->with($job);

        $subject->restart($job->getTicket());
    }

    /**
     * @expectedException \Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException
     */
    public function testUpdateThrowsExecptionIfJobIsNotManaged()
    {
        $job = new Job();
        $job->setTicket('JobTicket');

        $this->jobManager->expects($this->once())
            ->method('isManagerOf')
            ->with($job)
            ->willReturn(false);

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn(null);

        $this->subject->update($job);
    }

    public function testUpdateCopiesJobIfJobIsNotManaged()
    {
        $job = new Job();
        $job->setTicket('JobTicket');

        $managedJob = new Job();

        $this->jobManager->expects($this->once())
            ->method('isManagerOf')
            ->with($job)
            ->willReturn(false);

        $this->jobManager->expects($this->once())
            ->method('findByTicket')
            ->with($job->getTicket())
            ->willReturn($managedJob);

        $this->helper->expects($this->once())
            ->method('copyJob')
            ->with($job, $managedJob)
            ->willReturn($managedJob);

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with($managedJob);

        $this->subject->update($job);
    }

    public function testUpdateWithManagedJob()
    {
        $job = new Job();
        $job->setTicket('JobTicket');

        $this->jobManager->expects($this->once())
            ->method('isManagerOf')
            ->with($job)
            ->willReturn(true);

        $this->helper->expects($this->never())
            ->method('copyJob');

        $this->jobManager->expects($this->once())
            ->method('save')
            ->with($job);

        $this->subject->update($job);
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

    public static function provideUnterminatedStatus()
    {
        $result = [];
        foreach (Status::getUnterminatedStatus() as $status) {
            $result[] = [$status];
        }

        return $result;
    }

    public static function provideTerminatedStatus()
    {
        $result = [];
        foreach (Status::getTerminatedStatus() as $status) {
            $result[] = [$status];
        }

        return $result;
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
     * @param array $mockedMethods
     * @return Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockedSubject(array $mockedMethods)
    {
        /**
         * @var Manager|\PHPUnit_Framework_MockObject_MockObject $subject
         */
        $subject = $this->getMockBuilder(Manager::class)
            ->setConstructorArgs([
                $this->registry,
                $this->jobManager,
                $this->invoker,
                $this->loggerFactory,
                $this->logManager,
                $this->dispatcher,
                $this->helper,
                $this->locker,
                $this->validator,
                $this->logger
            ])
            ->setMethods($mockedMethods)
            ->getMock();

        $subject->setProducer($this->producer);

        return $subject;
    }

    /**
     * Creates an expectation that $helper->updateJob is called.
     *
     * @param JobInterface $expectedJob    The expected first argument passed to updateJob
     * @param Status       $status         The expected second argument passed to updateJob
     * @param mixed|null   $processingTime The optional expected third argument passed to updateJob
     */
    protected function expectsCallsUpdateJob(JobInterface $expectedJob, Status $status, $processingTime = null, $response = null)
    {
        if (null == $response) {
            $this->helper->expects($this->once())
                ->method('updateJob')
                ->with($expectedJob, $this->equalTo($status), $processingTime)
                ->willReturnCallback(
                    function (JobInterface $job) use ($status, $processingTime) {
                        $job->setStatus($status);
                        if ($processingTime != null) {
                            $job->setProcessingTime($processingTime);
                        }
                    }
                );
        } else {
            $this->helper->expects($this->once())
                ->method('updateJob')
                ->with($expectedJob, $this->equalTo($status), $processingTime, $response)
                ->willReturnCallback(
                    function (JobInterface $job) use ($status, $processingTime) {
                        $job->setStatus($status);
                        if ($processingTime != null) {
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
                    function ($name) use ($expectedEventName) {
                        return $expectedEventName != JobEvents::JOB_TERMINATED;
                    }
                )
            );
    }
}