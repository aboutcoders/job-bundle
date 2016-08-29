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

/**
 * Definition of a job.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface JobTypeInterface
{
    /**
     * @return string The service id of the job
     */
    public function getServiceId();

    /**
     * @return string The job type
     */
    public function getName();

    /**
     * @return callable The callable to be executed
     */
    public function getCallable();

    /**
     * @return string|null The class name of the callable
     */
    public function getClass();

    /**
     * @return string|null The method name of the callable
     */
    public function getMethod();

    /**
     * @return array An array of strings specifying the argument types used by the JMS serializer
     */
    public function getParameterTypes();

    /**
     * @return string|null The parameters type used to serialize job parameters with the JMS serializer
     */
    public function getParametersType();

    /**
     * @param array|null $parameterTypes An array of strings specifying the argument types used by the JMS serializer
     * @return void
     */
    public function setParameterTypes(array $parameterTypes = null);

    /**
     * @return string|null The response type used by the JMS serializer
     */
    public function getResponseType();

    /**
     * @param string|null $responseType The response type used by the JMS serializer
     * @return void
     */
    public function setResponseType($responseType = null);

    /**
     * @return int|null The Monolog\Logger log level
     */
    public function getLogLevel();

    /**
     * @param int $logLevel The Monolog\Logger log level
     * @return void
     */
    public function setLogLevel($logLevel);

    /**
     * @param $name string The name of the queue this job is assigned to
     * @return void
     */
    public function setQueue($name);

    /**
     * @return string The name of the queue this job is assigned to
     */
    public function getQueue();

    /**
     * Returns the name of the form class to enter the parameters of this job
     *
     * @param string $class The fully qualified class name
     * @return void
     */
    public function setFormType($class);

    /**
     * Sets the name of the form class to enter the parameters of this job
     *
     * @return string|null The fully qualified class name
     */
    public function getFormType();
}