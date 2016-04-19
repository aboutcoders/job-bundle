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

use Abc\Bundle\JobBundle\Event\JobEvents;

use Abc\Bundle\SchedulerBundle\Event\SchedulerEvents;
use Abc\Bundle\SchedulerBundle\Iterator\IteratorRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ServiceConfigurationTest extends KernelTestCase
{

    /** @var Application */
    private $application;
    /** @var ContainerInterface */
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
            ['abc.job.agent_manager', 'Abc\Bundle\JobBundle\Model\AgentManagerInterface'],
            ['abc.job.eraser', 'Abc\Bundle\JobBundle\Job\Report\EraserInterface'],
            ['abc.job.form.type.job', 'Abc\Bundle\JobBundle\Form\Type\JobType'],
            ['abc.job.form.type.message', 'Abc\Bundle\JobBundle\Form\Type\MessageType'],
            ['abc.job.job_manager', 'Abc\Bundle\JobBundle\Model\JobManagerInterface'],
            ['abc.job.listener.job', 'Abc\Bundle\JobBundle\Listener\RuntimeParameterProviderJobListener'],
            ['abc.job.listener.schedule', 'Abc\Bundle\JobBundle\Listener\ScheduleListener'],
            ['abc.job.logger.factory', 'Abc\Bundle\JobBundle\Job\Logger\FactoryInterface'],
            ['abc.job.log_manager', 'Abc\Bundle\JobBundle\Job\LogManagerInterface'],
            ['abc.job.mailer', 'Abc\Bundle\JobBundle\Job\Mailer\Mailer'],
            ['abc.job.manager', 'Abc\Bundle\JobBundle\Job\ManagerInterface'],
            ['abc.job.metadata_factory', 'Metadata\MetadataFactory'],
            ['abc.job.registry', 'Abc\Bundle\JobBundle\Job\JobTypeRegistry'],
            ['abc.job.schedule_manager', 'Abc\Bundle\JobBundle\Doctrine\ScheduleManager'],
            ['abc.job.serializer.generic_array_handler', 'Abc\Bundle\JobBundle\Serializer\Handler\GenericArrayHandler'],
            ['abc.job.sonata.notification.manager.message', 'Abc\Bundle\JobBundle\Sonata\ControlledMessageManager'],
            ['abc.job.lock_manager', 'Abc\Bundle\ResourceLockBundle\Model\LockManagerInterface'],
        ];
    }

    public function testJobListenerListensToJobPrepare()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');
        /** @var \Abc\Bundle\JobBundle\Listener\RuntimeParameterProviderJobListener|\PHPUnit_Framework_MockObject_MockObject $listener */
        $listener = $this->getMockBuilder('Abc\Bundle\JobBundle\Listener\RuntimeParameterProviderJobListener')->disableOriginalConstructor()->getMock();
        /** @var \Abc\Bundle\JobBundle\Event\ExecutionEvent|\PHPUnit_Framework_MockObject_MockObject $listener */
        $event = $this->getMockBuilder('Abc\Bundle\JobBundle\Event\ExecutionEvent')->disableOriginalConstructor()->getMock();

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
        /** @var \Abc\Bundle\JobBundle\Listener\RuntimeParameterProviderJobListener|\PHPUnit_Framework_MockObject_MockObject $listener */
        $listener = $this->getMockBuilder('Abc\Bundle\JobBundle\Listener\ScheduleListener')->disableOriginalConstructor()->getMock();
        /** @var \Abc\Bundle\SchedulerBundle\Event\SchedulerEvent|\PHPUnit_Framework_MockObject_MockObject $listener */
        $event = $this->getMockBuilder('Abc\Bundle\SchedulerBundle\Event\SchedulerEvent')->disableOriginalConstructor()->getMock();

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
}