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

use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class JobTestCase extends KernelTestCase
{
    /**
     * Asserts that job will be invoked with the given parameters.
     *
     * @param string $type              The job type
     * @param array  $parameters        The parameters the job will be invoked ith
     * @param array  $runtimeParameters An associative array containing additional runtime parameters (optional)
     */
    public function assertJobInvokedWithParams($type, array $parameters = array(), array $runtimeParameters = array())
    {
        $class             = static::getJobType($type)->getClass();
        $method            = static::getJobType($type)->getMethod();
        $runtimeParameters = array_merge($this->getDefaultRuntimeParameters(), $runtimeParameters);

        $deserializedParameters = $parameters;
        if (count($parameters) > 0) {
            $data = $this->serializeParameters($parameters);

            /**
             * @var array $deserializedParameters
             */
            $deserializedParameters = static::deserializeParameters($data, $type);
        }

        $this->assertEquals($parameters, $deserializedParameters, 'parameters are equal after serialization/deserialization');

        $resolvedParameters = $this->resolveArguments(static::getJobType($type), $parameters, $runtimeParameters);

        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods([$method])
            ->getMock();

        $callable = $mock->expects($this->once())
            ->method($method);

        // expect mock is called with the given parameters
        call_user_func_array([$callable, 'with'], $resolvedParameters);

        // execute the job
        call_user_func_array([$mock, $method], $resolvedParameters);
    }

    /**
     * Asserts that the registered job class matches the expected one
     *
     * @param string $type
     * @param string $expected
     */
    public function assertJobClass($type, $expected)
    {
        $callable = static::getJobType($type)->getCallable();
        $this->assertSame(get_class($callable[0]), $expected);
    }

    /**
     * @param $type
     */
    public static function assertJobIsRegistered($type)
    {
        static::assertTrue(static::getRegistry()->has($type));
    }

    /**
     * @param JobTypeInterface $jobType
     * @param array            $parameters
     *  @param array            $contextArray $context
     * @return array
     */
    private function resolveArguments(JobTypeInterface $jobType, array $parameters, array $contextArray)
    {
        $result = array();
        foreach ($jobType->getParameterTypes() as $parameterType) {
            if (0 === strpos($parameterType, '@')) {
                $key      = substr($parameterType, 1);
                $result[] = isset($contextArray[$key]) ? $contextArray[$key] : null;
            } else {
                $result[] = array_shift($parameters);
            }
        }

        return $result;
    }

    /**
     * @param array $parameters The parameters the job will be invoked with
     * @return string The serialized parameters
     */
    private static function serializeParameters(array $parameters)
    {
        return static::getSerializer()->serialize($parameters, 'json');
    }

    /**
     * @param string $data
     * @param string $type
     * @return array
     */
    private static function deserializeParameters($data, $type)
    {
        $type = static::getRegistry()->get($type)->getParametersType();

        return static::getSerializer()->deserialize($data, $type, 'json');
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
     * @return SerializerInterface
     */
    private static function getSerializer()
    {
        return static::$kernel->getContainer()->get('abc.job.serializer');
    }

    /**
     * @return array
     */
    private function getDefaultRuntimeParameters()
    {
        return [
            'manager' => $this->getMock(ManagerInterface::class),
            'logger'  => $this->getMock(LoggerInterface::class)
        ];
    }
}