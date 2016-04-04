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

use Monolog\Logger;

/**
 * Definition of a job.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobType implements JobTypeInterface
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $parameterTypes;

    /**
     * @var string
     */
    private $responseType;

    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var string
     */
    private $formClass;

    /**
     * @param string            $serviceId The name of the service within the container
     * @param string            $name The job type
     * @param callback|callable $callable The callable associated with the type
     * @param int|null          $logLevel The Monolog\Logger log level
     */
    function __construct($serviceId, $name, $callable, $logLevel = null)
    {
        if(!is_string($name) || strlen((string)$name) == 0)
        {
            throw new \InvalidArgumentException('$type must be a string');
        }
        if(!is_callable($callable))
        {
            throw new \InvalidArgumentException(sprintf('The callable defined for type "%s" is not callable', $name));
        }
        if($logLevel !== null && !is_int($logLevel))
        {
            throw new \InvalidArgumentException('$logLevel must be an integer value');
        }
        if($logLevel !== null && !in_array($logLevel, Logger::getLevels()))
        {
            throw new \InvalidArgumentException('$logLevel must be valid Monolog\Logger log level');
        }

        $this->serviceId = $serviceId;
        $this->name      = $name;
        $this->callable  = $callable;
        $this->logLevel  = $logLevel;
        $this->class     = !is_array($this->callable) ? null : get_class($this->callable[0]);
        $this->method    = !is_array($this->callable) ? null : $this->callable[1];
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterTypes()
    {
        return is_null($this->parameterTypes) ? [] : $this->parameterTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getParametersType()
    {
        if(is_null($this->parameterTypes) || empty($this->parameterTypes))
        {
            return null;
        }

        $serializedParams = [];
        foreach($this->getParameterTypes() as $parameterType)
        {
            if(0 !== strpos($parameterType, '@'))
            {
                $serializedParams[] = $parameterType;
            }
        }

        return sprintf('GenericArray<%s>', implode($serializedParams, ','));
    }

    /**
     * {@inheritdoc}
     */
    public function setParameterTypes(array $parameterTypes = null)
    {
        $this->parameterTypes = $parameterTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseType($responseType = null)
    {
        $this->responseType = $responseType;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormClass($class)
    {
        $this->formClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormClass()
    {
        return $this->formClass;
    }
}