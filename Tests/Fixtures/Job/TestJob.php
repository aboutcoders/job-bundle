<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Fixtures\Job;

use Abc\Bundle\JobBundle\Job\JobAwareInterface;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\ManagerAwareInterface;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Tests\Fixtures\App\Bundle\TestBundle\Entity\Entity;
use Abc\Bundle\SchedulerBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Annotation\JobResponse;
use Abc\ProcessControl\ControllerAwareInterface;
use Abc\ProcessControl\ControllerInterface;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TestJob implements JobAwareInterface, ManagerAwareInterface, ControllerAwareInterface, ContainerAwareInterface
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * {@inheritdoc}
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    /**
     * @param                 $maxIterations
     * @param int             $iterations
     * @param LoggerInterface $logger
     * @JobParameters({"integer","integer", "@logger"})
     */
    public function schedule($maxIterations, $iterations = 0, LoggerInterface $logger)
    {
        $logger->info('schedule');

        $iterations++;

        $this->job->setParameters(array($maxIterations, $iterations));

        if ($iterations >= $maxIterations) {
            $this->job->removeSchedules();

            $logger->info('removed schedule');
        }
    }

    /**
     * Creates a schedule
     *
     * @param string $type
     * @param string $expression
     * @JobParameters({"string","string"})
     */
    public function createSchedule($type, $expression)
    {
        $schedule = new Schedule();
        $schedule->setType($type);
        $schedule->setExpression($expression);

        $schedule = $this->job->createSchedule($type, $expression);
        $this->job->addSchedule($schedule);
    }

    /**
     * Removes a schedule
     *
     * @param LoggerInterface $logger
     * @JobParameters({"@logger"})
     */
    public function removeSchedule(LoggerInterface $logger)
    {
        $this->job->removeSchedules();

        $logger->info('removed schedule');
    }

    /**
     * Updates a schedule
     *
     * @param string $type
     * @param string $expression
     * @JobParameters({"string","string"})
     */
    public function updateSchedule($type, $expression)
    {
        $schedule = new Schedule();
        $schedule->setType($type);
        $schedule->setExpression($expression);

        $this->job->removeSchedules();
        $this->job->addSchedule($schedule);
    }

    /**
     * @param mixed $response
     * @return mixed $response
     * @JobParameters("Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse")
     * @JobResponse("Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse")
     */
    public function setResponse(TestResponse $response)
    {
        return $response;
    }

    /**
     * @param ManagerInterface $manager
     * @JobParameters("@manager")
     * @JobResponse("string")
     * @return null|string
     */
    public function manageJob(ManagerInterface $manager)
    {
        $job = $manager->addJob('log', ['addedJob']);

        return $job->getTicket();
    }

    /**
     * @JobResponse("string")
     * @return string|null
     */
    public function cancel()
    {
        while(!$this->controller->doExit()) {
            return 'running';
        }

        return 'cancelled';
    }

    /**
     * @param $message
     * @param $code
     * @throws \Exception
     * @JobParameters({"string","integer"})
     */
    public function throwException($message, $code)
    {
        throw new \Exception($message, $code);
    }

    /**
     * @param string          $message
     * @param LoggerInterface $logger
     * @JobParameters({"string", "@logger"})
     */
    public function log($message, LoggerInterface $logger)
    {
        $logger->debug($message);
        $logger->info($message);
        $logger->notice($message);
        $logger->warning($message);
        $logger->alert($message);
        $logger->critical($message);
    }

    /**
     * Logs the info message 'invoked parameterless'.
     *
     * @JobParameters({"string", "@logger"})
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function parameterless(LoggerInterface $logger)
    {
        $logger->debug('invoked parameterless');
    }

    public function throwDbalException()
    {
        /**
         * @var EntityManager $manager
         */
        $manager = $this->container->get('doctrine')->getManager();

        $entity = new Entity();
        $entity->setName('foobar');

        $manager->persist($entity);

        $entity = new Entity();
        $entity->setName('foobar');

        $manager->persist($entity);
    }
}