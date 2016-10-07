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

use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Invoker;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\ProcessControl\Factory;
use Abc\Bundle\JobBundle\Job\Queue\QueueConfig;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\LoggerAwareJob;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\ControllerAwareJob;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\JobAwareJob;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\ManagerAwareJob;
use Abc\ProcessControl\ControllerInterface;
use Metadata\MetadataFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataFactory;

    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerFactory;

    /**
     * @var Invoker
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->metadataFactory   = $this->createMock(MetadataFactoryInterface::class);
        $this->registry          = new JobTypeRegistry($this->metadataFactory, new QueueConfig());
        $this->manager           = $this->createMock(ManagerInterface::class);
        $this->controllerFactory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->subject           = new Invoker($this->registry);

        $this->subject->setManager($this->manager);
        $this->subject->setControllerFactory($this->controllerFactory);
    }

    /**
     * @dataProvider provideInvokeData
     * @param       $callable
     * @param       $expectedResponse
     * @param null  $parameters
     * @param array $parameterTypes
     * @param array $contextParameters
     */
    public function testInvoke($callable, $expectedResponse, $parameters = null, $parameterTypes = [], $contextParameters = [])
    {
        $context   = new Context($contextParameters);
        $serviceId = 'ServiceId';
        $type      = 'JobType';
        $job       = new Job($type, $parameters);

        $jobType = new JobType($serviceId, $type, $callable);
        $jobType->setParameterTypes($parameterTypes);

        $this->registry->register($jobType);

        $this->assertEquals($expectedResponse, $this->subject->invoke($job, $context));
    }

    public function testInvokeHandlesJobAwareJobs()
    {
        $serviceId = 'serviceId';
        $type      = 'callable-type';
        $callable  = new JobAwareJob();
        $jobType   = new JobType($serviceId, $type, array($callable, 'execute'));

        $job = new Job($type);

        $this->registry->register($jobType);

        $this->assertEquals('foobar', $this->subject->invoke($job, new Context()));
        $this->assertEquals($job, $callable->getJob());
    }

    public function testInvokeHandlesManagerAwareJobs()
    {
        $serviceId = 'serviceId';
        $type      = 'callable-type';
        $callable  = new ManagerAwareJob();
        $jobType   = new JobType($serviceId, $type, array($callable, 'execute'));

        $job = new Job($type);

        $this->registry->register($jobType);

        $this->assertEquals('foobar', $this->subject->invoke($job, new Context()));
        $this->assertEquals($this->manager, $callable->getManager());
    }

    public function testInvokeHandlesControllerAwareJobs()
    {
        $serviceId  = 'serviceId';
        $type       = 'callable-type';
        $callable   = new ControllerAwareJob();
        $jobType    = new JobType($serviceId, $type, array($callable, 'execute'));
        $controller = $this->createMock(ControllerInterface::class);

        $job = new Job($type);

        $this->registry->register($jobType);

        $this->controllerFactory->expects($this->once())
            ->method('create')
            ->with($job)
            ->willReturn($controller);

        $this->assertEquals('foobar', $this->subject->invoke($job, new Context()));
        $this->assertEquals($controller, $callable->getController());
    }

    /**
     * @param $withLogger
     * @dataProvider provideTrueFalse
     */
    public function testInvokeHandlesLoggerAwareJobs($withLogger)
    {
        $serviceId = 'serviceId';
        $type      = 'callable-type';
        $callable  = new LoggerAwareJob();
        $jobType   = new JobType($serviceId, $type, array($callable, 'execute'));
        $logger    = $this->createMock(LoggerInterface::class);
        $context   = new Context($withLogger ? ['logger' => $logger] : []);

        $job = new Job($type);

        $this->registry->register($jobType);

        $this->assertEquals('foobar', $this->subject->invoke($job, $context));
        if ($withLogger) {
            $this->assertSame($logger, $callable->getLogger());
        } else {
            $this->assertNull($callable->getLogger());
        }
    }


    /**
     * @return array
     */
    public static function provideInvokeData()
    {
        return [
            //[$callable, $expectedResponse, $parameters, $parameterTypes, $contextParameters],
            [function () {return 'foobar';}, 'foobar'],
            [function ($argument) {return $argument;}, 'foobar', ['foobar'], ['string']],
            [function () {}, null, null, ['string']],
            [function ($arg1 = null, $arg2) {}, null, null, ['string', '@contextParameter'], ['contextParameter' => 'foobar']],
            [function ($contextParameter) {return $contextParameter;}, 'foobar', [], ['@contextParameter'], ['contextParameter' => 'foobar']],
            [function ($parameter, $contextParameter) {
                return $parameter . $contextParameter;
            }, 'foobar', ['foo'], ['string', '@contextParameter'], ['contextParameter' => 'bar']],
            [function ($parameter, $contextParameter) {
                return [$parameter, $contextParameter];
            }, ['foo', null], ['foo'], ['string', '@contextParameter'], []],
            [function ($parameter1, $contextParameter2, $parameter3) {
                return [$parameter1, $contextParameter2, $parameter3];
            }, ['foo', null, 'bar'], ['foo', 'bar'], ['string', '@contextParameter', 'string'], []],
        ];
    }

    public static function provideTrueFalse()
    {
        return [
            [true],
            [false]
        ];
    }
}