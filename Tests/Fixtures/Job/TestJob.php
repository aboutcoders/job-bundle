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
use Abc\Bundle\JobBundle\Job\ScheduleBuilder;
use Abc\Bundle\JobBundle\Tests\Fixtures\App\Bundle\TestBundle\Entity\Entity;
use Abc\Bundle\SchedulerBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Annotation\ParamType;
use Abc\Bundle\JobBundle\Annotation\ReturnType;
use Abc\ProcessControl\ControllerAwareInterface;
use Abc\ProcessControl\ControllerInterface;
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
     * @ParamType("maxIterations", type="integer")
     * @ParamType("iterations", type="integer")
     * @ParamType("logger", type="@abc.logger")
     *
     * @param                 $maxIterations
     * @param int             $iterations
     * @param LoggerInterface $logger
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
     * @ParamType("type", type="string")
     * @ParamType("expression", type="string")
     *
     * @param string $type
     * @param string $expression
     */
    public function createSchedule($type, $expression)
    {
        $this->job->addSchedule(ScheduleBuilder::create($type, $expression));
    }

    /**
     * Removes a schedule
     *
     * @ParamType("logger", type="@abc.logger")
     *
     * @param LoggerInterface $logger

     */
    public function removeSchedule(LoggerInterface $logger)
    {
        $this->job->removeSchedules();

        $logger->info('removed schedule');
    }

    /**
     * Updates a schedule
     *
     * @ParamType("type", type="string")
     * @ParamType("expression", type="string")
     *
     * @param string $type
     * @param string $expression
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
     * @ParamType("response", type="Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse")
     * @ReturnType("Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse")
     *
     * @param mixed $response
     * @return mixed $response
     */
    public function setResponse(TestResponse $response)
    {
        return $response;
    }

    /**
     * @ParamType("manager", type="@abc.manager")
     * @ReturnType("string")
     *
     * @param ManagerInterface $manager
     * @return null|string
     */
    public function manageJob(ManagerInterface $manager)
    {
        $job = $manager->addJob('log', ['addedJob']);

        return $job->getTicket();
    }

    /**
     * @ReturnType("string")
     * @return string|null
     */
    public function cancel()
    {
        while (!$this->controller->doExit()) {
            return 'running';
        }

        return 'cancelled';
    }

    /**
     * @ParamType("message", type="string")
     * @ParamType("code", type="string")
     *
     * @param $message
     * @param $code
     * @throws \Exception
     */
    public function throwException($message, $code)
    {
        throw new \Exception($message, $code);
    }

    /**
     * @ParamType("message", type="string")
     * @ParamType("logger", type="@abc.logger")
     *
     * @param string          $message
     * @param LoggerInterface $logger
     */
    public function log($message, LoggerInterface $logger)
    {
        $logger->info($message);
    }

    /**
     * Logs the info message 'invoked parameterless'.
     *
     * @ParamType("logger", type="@abc.logger")
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
        $manager->flush();
    }
}