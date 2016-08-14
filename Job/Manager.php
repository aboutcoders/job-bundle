<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Event\TerminationEvent;
use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\QueueEngineInterface;
use Abc\Bundle\JobBundle\Job\Logger\FactoryInterface as LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Event\JobEvents;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Sonata\QueueEngine;
use Abc\Bundle\ResourceLockBundle\Exception\LockException;
use Abc\Bundle\ResourceLockBundle\Model\LockManagerInterface;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface as BaseScheduleInterface;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 * @author Wojciech Ciolko <wojciech.ciolko@aboutcoders.com>
 */
class Manager implements ManagerInterface
{
    const JOB_LOCK_PREFIX = 'job-';

    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var QueueEngine
     */
    protected $queueEngine;

    /**
     * @var JobManagerInterface
     */
    protected $jobManager;

    /**
     * @var Invoker
     */
    protected $invoker;

    /**
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * @var LogManagerInterface
     */
    protected $logManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var JobHelper
     */
    protected $helper;

    /**
     * @var LockManagerInterface
     */
    protected $locker;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param JobTypeRegistry          $registry
     * @param JobManagerInterface      $jobManager
     * @param Invoker                  $invoker
     * @param LoggerFactoryInterface   $loggerFactory
     * @param LogManagerInterface      $logManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobHelper                $helper
     * @param LockManagerInterface     $locker
     * @param LoggerInterface|null     $logger
     */
    public function __construct(
        JobTypeRegistry $registry,
        JobManagerInterface $jobManager,
        Invoker $invoker,
        LoggerFactoryInterface $loggerFactory,
        LogManagerInterface $logManager,
        EventDispatcherInterface $eventDispatcher,
        JobHelper $helper,
        LockManagerInterface $locker,
        LoggerInterface $logger = null)
    {
        $this->registry      = $registry;
        $this->jobManager    = $jobManager;
        $this->invoker       = $invoker;
        $this->loggerFactory = $loggerFactory;
        $this->logManager    = $logManager;
        $this->dispatcher    = $eventDispatcher;
        $this->helper        = $helper;
        $this->locker        = $locker;
        $this->logger        = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * @param QueueEngineInterface $queueEngine
     * @return void
     */
    public function setQueueEngine(QueueEngineInterface $queueEngine)
    {
        $this->queueEngine = $queueEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type, array $parameters = null, BaseScheduleInterface $schedule = null)
    {
        return $this->jobManager->create($type, $parameters, $schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function addJob($type, array $parameters = null, BaseScheduleInterface $schedule = null)
    {
        return $this->add($this->jobManager->create($type, $parameters, $schedule));
    }

    /**
     * {@inheritdoc}
     */
    public function add(JobInterface $job)
    {
        if (!$this->registry->has($job->getType())) {
            throw new \InvalidArgumentException(sprintf('A job of type "%s" is not registered', $job->getType()));
        }

        $this->jobManager->save($this->castJobIfTypeIsWrong($job));

        $this->logger->debug(
            'Added job with ticket {ticket} of type {type} with parameters {parameters}, schedules {schedules}',
            [
                'ticket'     => $job->getTicket(),
                'type'       => $job->getType(),
                'parameters' => $job->getParameters(),
                'schedules'  => $job->getSchedules()
            ]
        );

        if (!$job->hasSchedules()) {
            $this->publishJob($job->getType(), $job->getTicket());
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(JobInterface $job)
    {
        $this->logger->debug('Cancel job with ticket {ticket}', ['ticket' => $job->getTicket()]);

        if (!$this->jobManager->isManagerOf($job)) {
            $this->jobManager->findByTicket($job->getTicket());
        } else {
            /**
             * @var \Abc\Bundle\JobBundle\Model\JobInterface $job
             */
            $this->jobManager->refresh($job);
        }

        if (in_array($job->getStatus()->getValue(), Status::getTerminatedStatusValues())) {
            // should we throw an exception here?
            return null;
        }

        $isRunning = $job->getStatus() == Status::PROCESSING();

        $this->helper->updateJob($job, Status::CANCELLED());
        $this->jobManager->save($job);

        if (!$isRunning) {
            $this->dispatcher->dispatch(JobEvents::JOB_TERMINATED, new TerminationEvent($job));
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelJob($ticket)
    {
        return $this->cancel($this->findJob($ticket));
    }

    /**
     * {@inheritdoc}
     */
    public function get($ticket)
    {
        $this->logger->debug('Get job with ticket {ticket}', ['ticket' => $ticket]);

        return $this->findJob($ticket);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogs(JobInterface $job)
    {
        $this->logger->debug('Get logs for ticket {ticket}', ['ticket' => $job->getTicket()]);

        return $this->logManager->findByJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function getJobLogs($ticket)
    {
        return $this->getLogs($this->findJob($ticket));
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(Message $message)
    {
        $job = $this->findJob($message->getTicket());

        if ($job->getStatus() == Status::PROCESSING() || $job->getStatus() == Status::CANCELLED() || $job->getStatus() == Status::ERROR()) {

            $this->logger->debug('Skipped execution of job {ticket} because status is {status}', [
                'ticket' => $job->getType(),
                'status' => $job->getStatus()]);

            return;
        }
        try {
            //check if job is not running
            $this->locker->lock($this->getLockName($job));
        } catch (LockException $e) {
            $this->logger->warning('Job {job} is already running: {exception}', ['job' => $job, 'exception' => $e]);

            return;
        }

        $event = new ExecutionEvent($job, new Context());

        $this->dispatchExecutionEvent(JobEvents::JOB_PRE_EXECUTE, $event);

        $job->setStatus(Status::PROCESSING());
        $this->jobManager->save($job);

        $response       = null;
        $executionStart = microtime(true);

        try {
            $this->logger->debug(
                'Execute job of type {type} with ticket {ticket} and parameters {parameters}', [
                    'type'       => $job->getType(),
                    'ticket'     => $job->getTicket(),
                    'parameters' => $job->getParameters()
                ]
            );

            // invoke the job
            $response = $this->invoker->invoke($job, $event->getContext());

            $job->setResponse($response);

            if ($job->getStatus() != Status::CANCELLED()) {
                $status = $job->hasSchedules() ? Status::SLEEPING() : Status::PROCESSED();
            } else {
                $status = Status::CANCELLED();
            }

            $this->dispatchExecutionEvent(JobEvents::JOB_POST_EXECUTE, $event);
        } catch (DBALException $e) {
            $this->logger->critical('Job with ticket {ticket} could not be terminated due to exception {exception}', [
                'ticket'    => $job->getTicket(),
                'exception' => $e
            ]);

            throw $e;
        } catch (\Exception $e) {
            $this->logger->warning('Job execution {job} failed with the exception {exception}', ['job' => $job, 'exception' => $e]);

            if ($event->getContext()->has('logger')) {
                $event->getContext()->get('logger')->error($e->getMessage(), ['exception' => $e]);
            }

            $response = new ExceptionResponse($e->getMessage(), $e->getCode());
            $status   = Status::ERROR();
        }

        $this->releaseLock($job);

        $processingTime = $this->helper->calculateProcessingTime($executionStart);

        $this->helper->updateJob($job, $status, $processingTime, $response);
        $this->jobManager->save($job);

        if (in_array($job->getStatus()->getValue(), Status::getTerminatedStatusValues())) {
            $this->dispatcher->dispatch(JobEvents::JOB_TERMINATED, new TerminationEvent($job));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(JobInterface $job)
    {
        // TODO: Implement update() method.
    }

    /**
     * {@inheritdoc}
     */
    public function resume(JobInterface $job)
    {
        // TODO: Implement resume() method.
    }

    /**
     * {@inheritdoc}
     */
    public function resumeJob($ticket)
    {
        // TODO: Implement resumeJob() method.
    }

    /**
     *
     * @param string      $type         The job type
     * @param string      $ticket       The job ticket
     * @param string|null $callerTicket The ticket of a child job that is calling back
     * @throws \Exception
     * @return void
     */
    protected function publishJob($type, $ticket, $callerTicket = null)
    {
        $message = new Message($type, $ticket, $callerTicket);

        try {
            $this->logger->debug(
                'Published message with ticket {ticket} type {type} callerTicket {callerTicket} to queue backed', [
                    'ticket'       => $ticket,
                    'type'         => $type,
                    'callerTicket' => $callerTicket
                ]
            );

            $this->queueEngine->publish($message);
        } catch (\Exception $e) {
            $this->logger->critical('Failed to publish message to queue backend: {exception}', ['exception' => $e]);

            throw $e;
        }
    }

    /**
     * @param string $ticket
     * @return \Abc\Bundle\JobBundle\Model\JobInterface
     * @throws TicketNotFoundException If a job with the given ticket was not found
     */
    protected function findJob($ticket)
    {
        if ($job = $this->jobManager->findByTicket($ticket)) {
            return $job;
        }

        $this->logger->error('Job with ticket {ticket} not found', ['ticket' => $ticket]);

        throw new TicketNotFoundException($ticket);
    }

    /**
     * @param \Abc\Bundle\JobBundle\Job\JobInterface $job
     * @return \Abc\Bundle\JobBundle\Model\JobInterface
     */
    protected function castJobIfTypeIsWrong(JobInterface $job)
    {
        if (!$this->jobManager->isManagerOf($job)) {
            $newJob = $this->jobManager->create($job->getType(), $job->getParameters());
            foreach ($job->getSchedules() as $schedule) {
                $newJob->addSchedule($schedule);
            }
            $job = $newJob;
        }

        return $job;
    }

    /**
     * @param string         $eventName
     * @param ExecutionEvent $event
     * @return void
     */
    private function dispatchExecutionEvent($eventName, ExecutionEvent $event)
    {
        try {
            $this->logger->debug('Dispatch event {event} for job with ticket {ticket}', ['event' => $eventName, 'ticket' => $event->getJob()->getTicket()]);

            $this->dispatcher->dispatch($eventName, $event);
        } catch (\Exception $e) {
            $this->logger->critical('Event listener for {event} failed with exception {exception}', ['event' => JobEvents::JOB_PRE_EXECUTE, 'exception' => $e]);
        }
    }

    /**
     * Get lock name for job object
     *
     * @param JobInterface $job
     * @return string
     */
    private function getLockName(JobInterface $job)
    {
        return self::JOB_LOCK_PREFIX . $job->getTicket();
    }

    /**
     * @param JobInterface $job
     * @return bool
     */
    private function releaseLock($job)
    {
        $result = $this->locker->release($this->getLockName($job));
        $this->logger->info('Job {job} lock released', ['job' => $job]);

        return $result;
    }
}