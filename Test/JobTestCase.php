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

use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Event\JobEvents;
use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\Invoker;
use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Abc\Bundle\JobBundle\Validator\Constraints as AssertJob;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class JobTestCase extends KernelTestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
    }

    /**
     * Asserts that job will be invoked with the given parameters.
     *
     * @param string $type       The job type
     * @param array  $parameters The job parameters
     */
    public function assertInvokesJob($type, array $parameters = array())
    {
        $class  = static::getJobType($type)->getClass();
        $method = static::getJobType($type)->getMethod();

        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods([$method])
            ->getMock();

        $callable = $mock->expects($this->once())
            ->method($method);

        $resolvedParameters = $this->resolveParameters($type, $parameters);

        // expect mock is called with the given parameters
        call_user_func_array([$callable, 'with'], $resolvedParameters);

        // invoke the job
        call_user_func_array([$mock, $method], $resolvedParameters);
    }

    /**
     * Asserts that a job is registered.
     *
     * @param string $type              The job type
     * @param string $expectedServiceId The expected id of the service
     * @param string $expectedMethod    The expected name of the method
     */
    public function assertJobIsRegistered($type, $expectedServiceId, $expectedMethod)
    {
        $this->assertTrue(static::getRegistry()->has($type));
        $this->assertEquals($expectedServiceId, static::getRegistry()->get($type)->getServiceId());
        $this->assertEquals($expectedMethod, static::getRegistry()->get($type)->getMethod());
    }

    /**
     * Asserts that job parameters are valid.
     *
     * @param string $type       The job type
     * @param array  $parameters The job parameters
     * @return bool
     */
    public function assertValid($type, array $parameters)
    {
        return $this->validateParameters($type, $parameters)->count() == 0;
    }

    /**
     * Asserts that job parameters are not valid.
     *
     * @param string $type       The job type
     * @param array  $parameters The job parameters
     * @return bool
     */
    public function assertNotValid($type, array $parameters)
    {
        return $this->validateParameters($type, $parameters)->count() > 0;
    }

    /**
     * Resolves the parameters of a job.
     *
     * @param string $type       The job type
     * @param array  $parameters The job parameters
     * @return array The parameters the job can be invoked with
     */
    public function resolveParameters($type, array $parameters)
    {
        // serialize/deserialize parameters
        $deserializedParameters = $parameters;
        if (count($parameters) > 0) {
            $data = static::getSerializationHelper()->serializeParameters($type, $parameters);

            /**
             * @var array $deserializedParameters
             */
            $deserializedParameters = static::getSerializationHelper()->deserializeParameters($type, $data);
        }

        // Dispatch event to let listeners register runtime parameters
        $job = new Job();
        $job->setType($type);
        $job->setParameters($deserializedParameters);

        $event = new ExecutionEvent($job, new Context());

        static::getDispatcher()->dispatch(JobEvents::JOB_PRE_EXECUTE, $event);

        return Invoker::resolveParameters(static::getJobType($type), $event->getContext(), $deserializedParameters);
    }

    /**
     * Validates the parameters of a job.
     *
     * @param string $type       The job type
     * @param array  $parameters The job parameters
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public static function validateParameters($type, array $parameters)
    {
        return static::getValidator()->validate($parameters, new AssertJob\Parameters(['type' => $type]));
    }

    /**
     * @param string $type
     * @return JobTypeInterface
     */
    private static function getJobType($type)
    {
        return static::getRegistry()->get($type);
    }

    /**
     * @return JobTypeRegistry
     */
    private static function getRegistry()
    {
        return static::$kernel->getContainer()->get('abc.job.registry');
    }

    /**
     * @return SerializationHelper
     */
    private static function getSerializationHelper()
    {
        return static::$kernel->getContainer()->get('abc.job.serialization_helper');
    }

    /**
     * @return EventDispatcherInterface
     */
    private static function getDispatcher()
    {
        return static::$kernel->getContainer()->get('event_dispatcher');
    }

    /**
     * @return ValidatorInterface
     */
    private static function getValidator()
    {
        return static::$kernel->getContainer()->get('abc.job.validator');
    }
}