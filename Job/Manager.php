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
use Abc\Bundle\JobBundle\Job\Exception\ValidationFailedException;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Job\Queue\ProducerInterface;
use Abc\Bundle\JobBundle\Logger\LoggerFactoryInterface as LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Event\JobEvents;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\ResourceLockBundle\Exception\LockException;
use Abc\Bundle\ResourceLockBundle\Model\LockInterface;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface as BaseScheduleInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The job manager
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
     * @var ProducerInterface
     */
    protected $producer;

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
     * @var LockInterface
     */
    protected $locker;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var
     */
    private $jobLogger = array();

    /**
     * @param JobTypeRegistry          $registry
     * @param JobManagerInterface      $jobManager
     * @param Invoker                  $invoker
     * @param LoggerFactoryInterface   $loggerFactory
     * @param LogManagerInterface      $logManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobHelper                $helper
     * @param LockInterface            $locker
     * @param ValidatorInterface|null  $validator
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
        LockInterface $locker,
        ValidatorInterface $validator = null,
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
        $this->validator     = $validator;
        $this->logger        = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * @param ProducerInterface $producer
     * @return void
     */
    public function setProducer(ProducerInterface $producer)
    {
        $this->producer = $producer;
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
        if (null != $this->validator) {
            $this->logger->debug('Validate job');
            $errors = $this->validator->validate($job);
            if (count($errors) > 0) {
                $this->logger->debug('Validation failed with errors', ['errors' => $errors]);
                throw new ValidationFailedException($errors);
            }
        }

        if (!$this->jobManager->isManagerOf($job)) {
            $job = $this->helper->copyJob($job, $this->jobManager->create());
        }

        $job->setStatus(Status::REQUESTED());
        $job->setProcessingTime(0);

        $this->jobManager->save($job);

        $this->logger->info(sprintf('Added job %s of type "%s"', $job->getTicket(), $job->getType()), [
            'parameters' => $job->getParameters(),
            'schedules'  => $job->getSchedules()
        ]);

        if (!$job->hasSchedules()) {
            $this->publishJob($job);
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($ticket, $force = false)
    {
        $job = $this->findJob($ticket);

        if (Status::isTerminated($job->getStatus())) {
            return false;
        }

        $isProcessing = $job->getStatus() == Status::PROCESSING();
        $status       = $force ? Status::CANCELLED() : ($isProcessing ? Status::CANCELLING() : Status::CANCELLED());

        $this->helper->updateJob($job, $status);
        $this->jobManager->save($job);

        if ($force) {
            $this->locker->release($job->getTicket());
        }

        if (!$isProcessing || $force) {
            $this->dispatcher->dispatch(JobEvents::JOB_TERMINATED, new TerminationEvent($job));
            $message = $force ? 'Forced cancellation of job ' : 'Cancelled job ';
            $this->logger->info($message . $job->getTicket());
        } else {
            $this->logger->info('Request cancellation of job ' . $job->getTicket());
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ticket)
    {
        $this->logger->debug('Get job ' . $ticket);

        return $this->findJob($ticket);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogs($ticket)
    {
        $this->logger->debug('Get logs of job ' . $ticket);

        return $this->logManager->findByJob($this->findJob($ticket));
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(Message $message)
    {
        $job = $this->findJob($message->getTicket());

        if ($job->getStatus() == Status::PROCESSING() || $job->getStatus() == Status::CANCELLED() || $job->getStatus() == Status::ERROR()) {

            $this->logger->notice(sprintf('Skipped execution of job %s (status: %s)', $job->getTicket(), $job->getStatus()));

            return;
        }
        try {
            $this->locker->lock($this->getLockName($job));
        } catch (LockException $e) {
            $this->logger->warning('Failed to get lock for job ' . $job->getTicket());

            return;
        }

        $event = new ExecutionEvent($job, new Context());

        $this->dispatchExecutionEvent(JobEvents::JOB_PRE_EXECUTE, $event);

        $job->setStatus(Status::PROCESSING());
        $this->jobManager->save($job);

        $response       = null;
        $executionStart = microtime(true);

        try {
            $this->logger->debug(sprintf('Execute job %s of type "%s"', $job->getTicket(), $job->getType()), [
                'parameters' => $job->getParameters()
            ]);

            // invoke the job
            $response = $this->invoker->invoke($job, $event->getContext());

            $job->setResponse($response);

            if ($job->getStatus() != Status::CANCELLED()) {
                $status = $job->hasSchedules() ? Status::SLEEPING() : Status::PROCESSED();
            } else {
                $status = Status::CANCELLED();
            }

            $this->dispatchExecutionEvent(JobEvents::JOB_POST_EXECUTE, $event);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('Failed to execute job %s (Error: $s)', $job->getTicket(), $e->getMessage()), [
                'job'       => $job,
                'exception' => $e
            ]);

            $this->getJobLogger($job)->error($e->getMessage(), ['exception' => $e]);

            $response = new ExceptionResponse($e);
            $status   = Status::ERROR();
        }

        $this->releaseLock($job);

        $processingTime = $this->helper->calculateProcessingTime($executionStart);

        $this->helper->updateJob($job, $status, $processingTime, $response);
        $this->jobManager->save($job);

        if (Status::isTerminated($job->getStatus())) {
            $this->dispatcher->dispatch(JobEvents::JOB_TERMINATED, new TerminationEvent($job));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restart($ticket)
    {
        $this->logger->debug('Restart job ' . $ticket);

        $job = $this->findJob($ticket);

        return $this->add($job);
    }

    /**
     * {@inheritdoc}
     */
    public function update(JobInterface $job)
    {
        $existingJob = $this->jobManager->findByTicket($job->getTicket());
        if (null == $existingJob) {
            return $this->add($job);
        }

        $this->logger->debug('Update job ' . $job->getTicket(), ['job' => $job]);

        $job = $this->helper->copyJob($job, $existingJob);

        if (null != $this->validator) {
            $errors = $this->validator->validate($job);
            if (count($errors) > 0) {
                throw new ValidationFailedException($errors);
            }
        }

        $this->jobManager->save($job);

        $this->getJobLogger($job)->debug('Updated job', ['job' => $job]);

        return $job;
    }

    /**
     * @param JobInterface $job
     * @throws \Exception
     */
    protected function publishJob(JobInterface $job)
    {
        $message = new Message($job->getType(), $job->getTicket());

        try {
            $this->producer->produce($message);
        } catch (\Exception $e) {
            $this->logger->critical(sprintf('Failed to publish message for job %s (Error: %s)', $job->getTicket(), $e->getMessage()), [
                'job'       => $job,
                'exception' => $e
            ]);

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
     * @param string         $eventName
     * @param ExecutionEvent $event
     * @return void
     */
    private function dispatchExecutionEvent($eventName, ExecutionEvent $event)
    {
        try {
            $this->logger->debug(sprintf('Dispatch event %s for job %s', $eventName, $event->getJob()->getTicket()));

            $this->dispatcher->dispatch($eventName, $event);
        } catch (\Exception $e) {
            $this->logger->critical(sprintf('An event listener for event %s threw an exception (Error: %s)', $eventName, $e->getMessage()), ['exception' => $e]);
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
     * @return void
     */
    private function releaseLock($job)
    {
        $this->locker->release($this->getLockName($job));

        $this->logger->debug('Released lock for job ' . $job->getTicket());
    }

    /**
     * @param JobInterface $job
     * @return LoggerInterface
     */
    private function getJobLogger(JobInterface $job)
    {
        if (!isset($this->jobLogger[0]) || $job !== $this->jobLogger[0]) {
            $this->jobLogger[0] = $job;
            $this->jobLogger[1] = $this->loggerFactory->create($job);
        }

        return $this->jobLogger[1];
    }
}