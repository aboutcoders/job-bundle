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
     * Returns the parameter types of a job as specified in the annotation @ParamType.
     *
     * The order of elements in the  array reflects the order of the parameters in the method signature of the job.
     *
     * @return array|string[] The types of parameters the job can be invoked with
     */
    public function getParameterTypes();

    /**
     * @param array $types The types of parameters the job can be invoked with
     * @return void
     */
    public function setParameterTypes(array $types = array());

    /**
     * Returns the serializable parameter types of a job as specified in the annotation @ParamType.
     *
     * The order of elements in the  array reflects the order of the parameters in the method signature of the job.
     *
     * @return array|string[] An array of types the job can be invoked with
     */
    public function getSerializableParameterTypes();

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
}