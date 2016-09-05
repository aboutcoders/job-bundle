<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Test;

use Abc\Bundle\EnumSerializerBundle\Serializer\Handler\EnumHandler;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Serializer\EventDispatcher\JobDeserializationSubscriber;
use Abc\Bundle\JobBundle\Serializer\Handler\JobParameterArrayHandler;
use Abc\Bundle\JobBundle\Validator\Constraint\JobType;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SerializationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(JobTypeRegistry::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->setUpSerializer($this->registry);
    }

    /**
     * @param JobTypeRegistry $registry
     */
    protected function setUpSerializer(JobTypeRegistry $registry)
    {
        EnumHandler::register(Status::class);
        $enumHandler = new EnumHandler();

        $this->serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistry $handlerRegistry) use ($registry, $enumHandler) {
                $handlerRegistry->registerSubscribingHandler(new JobParameterArrayHandler($registry));
                $handlerRegistry->registerSubscribingHandler($enumHandler);
            })
            ->configureListeners(function (EventDispatcher $dispatcher) {
                $dispatcher->addSubscriber(new JobDeserializationSubscriber());
            })
            ->build();
    }

    /**
     * @param Job $job
     * @return void
     */
    protected function setUpRegistry(Job $job)
    {
        if ($job->getParameters() != null && is_array($job->getParameters()) && count($job->getParameters()) > 0) {
            $parameterTypes = [];
            foreach ($job->getParameters() as $parameter) {
                $parameterTypes[] = (is_object($parameter)) ? get_class($parameter) : gettype($parameter);
            }

            $jobType = $this->getMockBuilder(JobType::class)->disableOriginalConstructor()->getMock();

            $jobType->expects($this->once())
                ->method('getParameterTypes')
                ->willReturn($parameterTypes);

            $this->registry->expects($this->once())
                ->method('get')
                ->with($job->getType())
                ->willReturn($jobType);
        }
    }
}