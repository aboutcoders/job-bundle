<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Doctrine;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Model\Job as BaseJob;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 *
 * @ExclusionPolicy("all")
 */
class Job extends BaseJob
{
    /**
     * @var SerializerInterface
     */
    protected static $serializer;

    /**
     * @var JobTypeRegistry
     */
    protected static $registry;

    /**
     * @var string|null
     */
    protected $serializedParameters;

    /**
     * @var string|null
     */
    protected $serializedResponse;

    /**
     * @return string|null The serialized parameters
     */
    protected function getSerializedParameters()
    {
        return $this->serializedParameters;
    }

    /**
     * @param string|null $serializedParameters The serialized parameters
     * @return void
     */
    protected function setSerializedParameters($serializedParameters = null)
    {
        $this->serializedParameters = $serializedParameters;
    }

    /**
     * @return null|string
     */
    protected function getSerializedResponse()
    {
        return $this->serializedResponse;
    }

    /**
     * @param null|string $serializedResponse
     */
    protected function setSerializedResponse($serializedResponse)
    {
        $this->serializedResponse = $serializedResponse;
    }

    /**
     * @param array|null $parameters
     * @throws \InvalidArgumentException If serialization of parameters fails
     * @throws \RuntimeException
     * @return void
     */
    public function setParameters($parameters = null)
    {
        $serializer = static::getSerializer();

        parent::setParameters($parameters);

        try
        {
            $this->setSerializedParameters($parameters == null ? null : $serializer->serialize($parameters, 'json'));
        }
        catch(\Exception $e)
        {
            throw new \InvalidArgumentException('Failed to serialize parameters', null, $e);
        }
    }

    /**
     * @return array|null
     * @throws \Abc\Bundle\JobBundle\Job\JobTypeNotFoundException
     * @throws \RuntimeException
     */
    public function getParameters()
    {
        if(is_null(parent::getParameters()) && !is_null($this->getSerializedParameters()))
        {
            // deserialize
            $type       = static::getRegistry()->get($this->getType())->getParametersType();
            $parameters = static::getSerializer()->deserialize($this->getSerializedParameters(), $type, 'json');

            parent::setParameters($parameters);
        }

        return parent::getParameters();
    }

    /**
     * @param mixed|null $response
     * @throws \InvalidArgumentException If serialization of parameters fails
     * @throws \RuntimeException
     * @return void
     */
    public function setResponse($response = null)
    {
        $serializer = static::getSerializer();

        parent::setResponse($response);

        try
        {
            $this->setSerializedResponse($response == null ? null : $serializer->serialize($response, 'json'));
        }
        catch(\Exception $e)
        {
            throw new \InvalidArgumentException('Failed to serialize response', null, $e);
        }
    }

    /**
     * @return mixed|null
     * @throws \Abc\Bundle\JobBundle\Job\JobTypeNotFoundException
     * @throws \RuntimeException
     */
    public function getResponse()
    {
        if(is_null(parent::getResponse()) && !is_null($this->getSerializedResponse()))
        {
            // deserialize
            $type     = static::getRegistry()->get($this->getType())->getResponseType();
            $response = static::getSerializer()->deserialize($this->getSerializedResponse(), $type, 'json');

            parent::setResponse($response);
        }

        return parent::getResponse();
    }

    /**
     *
     * @return SerializerInterface
     * @throws \RuntimeException If the serializer is not set
     */
    protected static function getSerializer()
    {
        if(is_null(static::$serializer))
        {
            throw new \RuntimeException('The serializer is null');
        }

        return static::$serializer;
    }

    /**
     * @param SerializerInterface $serializer
     * @return void
     */
    public static function setSerializer(SerializerInterface $serializer)
    {
        static::$serializer = $serializer;
    }

    /**
     * @return JobTypeRegistry
     * @throws \RuntimeException If the registry is not set
     */
    protected static function getRegistry()
    {
        if(is_null(static::$registry))
        {
            throw new \RuntimeException('The registry is null');
        }

        return static::$registry;
    }

    /**
     * @param JobTypeRegistry $registry
     * @return void
     */
    public static function setRegistry(JobTypeRegistry $registry)
    {
        static::$registry = $registry;
    }
}