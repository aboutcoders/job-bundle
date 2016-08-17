<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration;

use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Event\JobEvents;
use Abc\Bundle\JobBundle\Form\Type\JobType;
use Abc\Bundle\JobBundle\Form\Type\MessageType;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Logger\FactoryInterface;
use Abc\Bundle\JobBundle\Job\LogManagerInterface;
use Abc\Bundle\JobBundle\Job\Mailer\Mailer;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\ProcessControl\Factory;
use Abc\Bundle\JobBundle\Job\ProcessControl\JobController;
use Abc\Bundle\JobBundle\Job\Report\EraserInterface;
use Abc\Bundle\JobBundle\Listener\JobListener;
use Abc\Bundle\JobBundle\Listener\ScheduleListener;
use Abc\Bundle\JobBundle\Model\AgentManagerInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Serializer\Handler\GenericArrayHandler;
use Abc\Bundle\JobBundle\Sonata\ControlledMessageManager;
use Abc\Bundle\JobBundle\Validator\Constraint\JobTypeValidator;
use Abc\Bundle\ResourceLockBundle\Model\LockManagerInterface;
use Abc\Bundle\SchedulerBundle\Doctrine\ScheduleManager;
use Abc\Bundle\SchedulerBundle\Event\SchedulerEvent;
use Abc\Bundle\SchedulerBundle\Event\SchedulerEvents;
use Abc\Bundle\SchedulerBundle\Iterator\IteratorRegistryInterface;
use Metadata\MetadataFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ServiceTest extends KernelTestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();

        $this->container   = static::$kernel->getContainer();
        $this->application = new Application(static::$kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
    }

    /**
     * @param string $service
     * @param string $type
     * @dataProvider getServices
     */
    public function testGetFromContainer($service, $type)
    {
        $subject = $this->container->get($service);

        $this->assertInstanceOf($type, $subject);
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return [
            ['abc.job.agent_manager', AgentManagerInterface::class],
            ['abc.job.eraser', EraserInterface::class],
            ['abc.job.form.type.job', JobType::class],
            ['abc.job.job_manager', JobManagerInterface::class],
            ['abc.job.listener.job', JobListener::class],
            ['abc.job.listener.schedule', ScheduleListener::class],
            ['abc.job.logger.factory', FactoryInterface::class],
            ['abc.job.log_manager', LogManagerInterface::class],
            ['abc.job.mailer', Mailer::class],
            ['abc.job.manager', ManagerInterface::class],
            ['abc.job.metadata_factory', MetadataFactory::class],
            ['abc.job.registry', JobTypeRegistry::class],
            ['abc.job.schedule_manager', ScheduleManager::class],
            ['abc.job.serializer.generic_array_handler', GenericArrayHandler::class],
            ['abc.job.sonata.notification.manager.message', ControlledMessageManager::class],
            ['abc.job.lock_manager', LockManagerInterface::class],
            ['abc.job.controller_factory', Factory::class],
            ['abc.job.validator.job_type', JobTypeValidator::class]
        ];
    }

    public function testJobListenerListensToJobPrepare()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');
        /** @var \Abc\Bundle\JobBundle\Listener\JobListener|\PHPUnit_Framework_MockObject_MockObject $listener */
        $listener = $this->getMockBuilder(JobListener::class)->disableOriginalConstructor()->getMock();
        /** @var \Abc\Bundle\JobBundle\Event\ExecutionEvent|\PHPUnit_Framework_MockObject_MockObject $listener */
        $event = $this->getMockBuilder(ExecutionEvent::class)->disableOriginalConstructor()->getMock();

        $this->container->set('abc.job.listener.job', $listener);

        $listener->expects($this->once())
            ->method('onPreExecute')
            ->with($event);

        $dispatcher->dispatch(JobEvents::JOB_PRE_EXECUTE, $event);
    }

    public function testScheduleListenerListensToSchedule()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');
        /** @var \Abc\Bundle\JobBundle\Listener\JobListener|\PHPUnit_Framework_MockObject_MockObject $listener */
        $listener = $this->getMockBuilder(ScheduleListener::class)->disableOriginalConstructor()->getMock();
        /** @var \Abc\Bundle\SchedulerBundle\Event\SchedulerEvent|\PHPUnit_Framework_MockObject_MockObject $listener */
        $event = $this->getMockBuilder(SchedulerEvent::class)->disableOriginalConstructor()->getMock();

        $this->container->set('abc.job.listener.schedule', $listener);

        $listener->expects($this->once())
            ->method('onSchedule')
            ->with($event);

        $dispatcher->dispatch(SchedulerEvents::SCHEDULE, $event);
    }

    public function testScheduleIteratorIsRegistered()
    {
        /** @var IteratorRegistryInterface $registry */
        $registry = $this->container->get('abc.scheduler.iterator_registry');

        $this->assertCount(1, $registry->all());
    }

    public function testControllerFactory()
    {
        /**
         * @var Factory $factory
         */
        $factory = $this->container->get('abc.job.controller_factory');

        /**
         * @var JobInterface $job
         */
        $job = $this->getMock(Job::class);

        $controller = $factory->create($job);

        $this->assertInstanceOf(JobController::class, $controller);
    }
}