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
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface as BaseScheduleInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Manager implements ManagerInterface
{
    /** @var JobTypeRegistry */
    protected $registry;
    /** @var QueueEngine */
    protected $queueEngine;
    /** @var JobManagerInterface */
    protected $jobManager;
    /** @var Invoker */
    protected $invoker;
    /** @var LoggerFactoryInterface */
    protected $loggerFactory;
    /** @var LogManagerInterface */
    protected $logManager;
    /** @var EventDispatcherInterface */
    protected $dispatcher;
    /** @var JobHelper */
    protected $helper;
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param JobTypeRegistry          $registry
     * @param JobManagerInterface      $jobManager
     * @param Invoker                  $invoker
     * @param LoggerFactoryInterface   $loggerFactory
     * @param LogManagerInterface      $logManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobHelper                $helper
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
        LoggerInterface $logger = null)
    {
        $this->registry        = $registry;
        $this->jobManager      = $jobManager;
        $this->invoker         = $invoker;
        $this->loggerFactory   = $loggerFactory;
        $this->logManager      = $logManager;
        $this->dispatcher      = $eventDispatcher;
        $this->helper          = $helper;
        $this->logger          = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * @param QueueEngineInterface $queueEngine
     * @return void
     */
    public function setQueueEngine(QueueEngineInterface $queueEngine)
    {
        $this->queueEngine = $queueEngine;
    }

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
        if(!$this->registry->has($job->getType()))
        {
            throw new \InvalidArgumentException(sprintf('A job of type "%s" is not registered', $job->getType()));
        }

        $this->jobManager->save($this->castJobIfTypeIsWrong($job));

        $this->logger->debug(
            'Added job with ticket {ticket} of type {type} with parameters {parameters}, schedules {schedules}',
            array(
                'ticket' => $job->getTicket(),
                'type' => $job->getType(),
                'parameters' => $job->getParameters(),
                'schedules' => $job->getSchedules()
            )
        );

        if(!$job->hasSchedules())
        {
            $this->publishJob($job->getType(), $job->getTicket());
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(JobInterface $job)
    {
        $this->logger->debug('Cancel job with ticket {ticket}', array('ticket' => $job->getTicket()));

        $class = $this->jobManager->getClass();
        if(!$job instanceof $class)
        {
            $this->jobManager->findByTicket($job->getTicket());
        }

        /** @var \Abc\Bundle\JobBundle\Model\JobInterface $job */
        $this->helper->updateJob($job, Status::CANCELLED());
        $this->jobManager->save($job);

        $this->dispatcher->dispatch(JobEvents::JOB_TERMINATED, new TerminationEvent($job));
    }

    /**
     * {@inheritdoc}
     */
    public function cancelJob($ticket)
    {
        $this->cancel($this->findJob($ticket));
    }

    /**
     * {@inheritdoc}
     */
    public function get($ticket)
    {
        $this->logger->debug('Get job with ticket {ticket}', array('ticket' => $ticket));

        return $this->findJob($ticket);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogs(JobInterface $job)
    {
        $this->logger->debug('Get logs for ticket {ticket}', array('ticket' => $job->getTicket()));

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

        if($job->getStatus() == Status::CANCELLED())
        {
            $this->logger->debug('Skipped execution of job because has been CANCELLED');

            return;
        }

        $event = new ExecutionEvent($job, new Context());

        $this->dispatchExecutionEvent(JobEvents::JOB_PRE_EXECUTE, $event);

        $job->setStatus(Status::PROCESSING());
        $this->jobManager->save($job);

        $response       = null;
        $executionStart = microtime(true);

        try
        {
            $this->logger->debug(
                'Execute job of type {type} with ticket {ticket} and parameters {parameters}',
                array(
                    'type' => $job->getType(),
                    'ticket' => $job->getTicket(),
                    'parameters' => $job->getParameters()
                )
            );

            // invoke the job
            $response = $this->invoker->invoke($job, $event->getContext());

            $job->setResponse($response);

            $status = $job->hasSchedules() ? Status::SLEEPING() : Status::PROCESSED();

            $this->dispatchExecutionEvent(JobEvents::JOB_POST_EXECUTE, $event);
        }
        catch(\Exception $e)
        {
            $this->logger->warning('Job execution {job} failed with the exception {exception}', array('job' => $job, 'exception' => $e));

            if($event->getContext()->has('logger'))
            {
                $event->getContext()->get('logger')->error($e->getMessage(), array('exception' => $e));
            }

            $response = new ExceptionResponse($e->getMessage(), $e->getCode());
            $status   = Status::ERROR();
        }

        $processingTime = $this->helper->calculateProcessingTime($executionStart);

        $this->helper->updateJob($job, $status, $processingTime, $response);
        $this->jobManager->save($job);

        if(in_array($job->getStatus()->getValue(), Status::getTerminatedStatusValues()))
        {
            $this->dispatcher->dispatch(JobEvents::JOB_TERMINATED, new TerminationEvent($job));
        }
    }

    /**
     *
     * @param string      $type The job type
     * @param string      $ticket The job ticket
     * @param string|null $callerTicket The ticket of a child job that is calling back
     * @throws \Exception
     * @return void
     */
    protected function publishJob($type, $ticket, $callerTicket = null)
    {
        $message = new Message($type, $ticket, $callerTicket);

        try
        {
            $this->logger->debug(
                'Published message with ticket {ticket} type {type} callerTicket {callerTicket} to queue backed',
                array(
                    'ticket' => $ticket,
                    'type' => $type,
                    'callerTicket' => $callerTicket
                )
            );

            $this->queueEngine->publish($message);
        }
        catch(\Exception $e)
        {
            $this->logger->critical('Failed to publish message to queue backend: {exception}', array('exception' => $e));

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
        if($job = $this->jobManager->findByTicket($ticket))
        {
            return $job;
        }

        $this->logger->error('Job with ticket {ticket} not found', array('ticket' => $ticket));

        throw new TicketNotFoundException($ticket);
    }

    /**
     * @param \Abc\Bundle\JobBundle\Job\JobInterface $job
     * @return \Abc\Bundle\JobBundle\Model\JobInterface
     */
    protected function castJobIfTypeIsWrong(JobInterface $job)
    {
        $entityClass = $this->jobManager->getClass();

        if(!$job instanceof $entityClass)
        {
            $newJob = $this->jobManager->create($job->getType(), $job->getParameters());
            foreach($job->getSchedules() as $schedule)
            {
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
        try
        {
            $this->logger->debug('Dispatch event {event} for job with ticket {ticket}', array('event' => $eventName, 'ticket' => $event->getJob()->getTicket()));

            $this->dispatcher->dispatch($eventName, $event);
        }
        catch(\Exception $e)
        {
            $this->logger->critical('Event listener for {event} failed with exception {exception}', array('event' => JobEvents::JOB_PRE_EXECUTE, 'exception' => $e));
        }
    }
}