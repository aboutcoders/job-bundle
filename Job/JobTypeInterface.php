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
     * Returns the parameter types of a job.
     *
     * The index of the elements corresponds to the position of the parameter in the method signature of the job.
     *
     * @return array|string[] The types of parameters the job can be invoked with
     */
    public function getParameterTypes();

    /**
     * Returns the type of a parameter.
     *
     * @param integer $index The index of the parameter within the method signature (starting with zero)
     * @return string
     */
    public function getParameterType($index);

    /**
     * @param array $types The types of parameters the job can be invoked with
     * @return void
     */
    public function setParameterTypes(array $types);

    /**
     * @param integer $index The index of the parameter within the method signature (starting with zero)
     * @return array
     */
    public function getParameterTypeOptions($index = null);

    /**
     * @param array $options
     * @return void
     */
    public function setParameterTypeOptions(array $options);

    /**
     * @return string The return type
     */
    public function getReturnType();

    /**
     * @param string $type The return type
     * @return void
     */
    public function setReturnType($type);

    /**
     * @param array $options
     * @return void
     */
    public function setReturnTypeOptions(array $options);

    /**
     * @return array
     */
    public function getReturnTypeOptions();

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
     * Returns the indices of the parameters within the method signature of the job that are serializable
     *
     * @return array
     */
    public function getIndicesOfSerializableParameters();
}