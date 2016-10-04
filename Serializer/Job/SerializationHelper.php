<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Serializer\Job;

use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Serializer\DeserializationContext;
use Abc\Bundle\JobBundle\Serializer\SerializationContext;
use Abc\Bundle\JobBundle\Serializer\SerializerInterface;
use JMS\Serializer\Context;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SerializationHelper
{
    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param JobTypeRegistry     $registry
     * @param SerializerInterface $serializer
     */
    public function __construct(JobTypeRegistry $registry, SerializerInterface $serializer)
    {
        $this->registry   = $registry;
        $this->serializer = $serializer;
    }

    /**
     * Serializes the parameters of a job.
     *
     * @param string $type The job type
     * @param array  $parameters
     * @return string
     */
    public function serializeParameters($type, array $parameters)
    {
        $jobType = $this->registry->get($type);
        $indices = $jobType->getIndicesOfSerializableParameters();
        if (count($indices) < count($parameters)) {
            throw new \InvalidArgumentException(sprintf('More parameters provided for serialization than defined for job "%s"', $type));
        }

        $i = 0;
        $serializedParameters = array();
        foreach ($parameters as $parameter) {
            if (null == $parameter) {
                $serializedParameters[] = null;
            } else {
                $serializedParameters[] = $this->serializer->serialize($parameter, 'json', $this->getParamSerializationContext($jobType, $indices[$i]));
            }
            $i++;
        }

        $data = json_encode($serializedParameters);
        if (false === $data) {
            throw new \RuntimeException(sprintf('Serialization failed with error "%s"', json_last_error_msg()));
        }

        return $data;
    }

    /**
     * Deserializes the parameters of a job.
     *
     * @param string $type The job type
     * @param string $data The serialized parameters
     * @return array The deserialized parameters
     * @throws \InvalidArgumentException If no serializable parameters are defined for the job type
     */
    public function deserializeParameters($type, $data)
    {
        $jobType = $this->registry->get($type);
        $indices = $jobType->getIndicesOfSerializableParameters();

        $serializedParameters = json_decode($data, 1);
        if (false === $serializedParameters) {
            throw new \RuntimeException(sprintf('Deserialization failed with error "%s"', json_last_error_msg()));
        }

        if (count($indices) < count($serializedParameters)) {
            throw new \InvalidArgumentException(sprintf('The serialized data contains more parameters than defined for job "%s"', $type));
        }

        $parameters = array();
        foreach ($serializedParameters as $index => $data) {
            if (null === $data) {
                $parameters[] = null;
            } else {
                $parameters[] = $this->serializer->deserialize($data, $jobType->getParameterType($indices[$index]), 'json', $this->getParamDeserializationContext($jobType, $indices[$index]));
            }
        }

        return $parameters;
    }

    /**
     * Serializes the return value of a job.
     *
     * @param string $type The job type
     * @param mixed  $value
     * @return string
     */
    public function serializeReturnValue($type, $value)
    {
        $jobType = $this->registry->get($type);

        return $this->serializer->serialize($value, 'json', $this->getResponseSerializationContext($jobType));
    }

    /**
     * Deserializes the return of a job.
     *
     * @param string $type The job type
     * @param string $data
     * @return mixed
     */
    public function deserializeReturnValue($type, $data)
    {
        $jobType = $this->registry->get($type);

        return $this->serializer->deserialize($data, $jobType->getReturnType(), 'json', $this->getResponseDeserializationContext($jobType));
    }

    /**
     * @param JobTypeInterface $jobType
     * @param integer          $index The index of the parameter (starting with zero)
     * @return SerializationContext
     */
    protected function getParamSerializationContext(JobTypeInterface $jobType, $index)
    {
        $context = new SerializationContext();

        $this->configureContext($context, $jobType->getParameterTypeOptions($index));

        return $context;
    }

    /**
     * @param JobTypeInterface $jobType
     * @param integer          $index The index of the parameter (starting with zero)
     * @return DeserializationContext
     */
    protected function getParamDeserializationContext(JobTypeInterface $jobType, $index)
    {
        $context = new DeserializationContext();

        $this->configureContext($context, $jobType->getParameterTypeOptions($index));

        return $context;
    }

    /**
     * @param JobTypeInterface $jobType
     * @return SerializationContext
     */
    protected function getResponseSerializationContext(JobTypeInterface $jobType)
    {
        $context = new SerializationContext();

        $this->configureContext($context, $jobType->getReturnTypeOptions());

        return $context;
    }

    /**
     * @param JobTypeInterface $jobType
     * @return DeserializationContext
     */
    protected function getResponseDeserializationContext(JobTypeInterface $jobType)
    {
        $context = new DeserializationContext();

        $this->configureContext($context, $jobType->getReturnTypeOptions());

        return $context;
    }

    /**
     * @param Context $context
     * @param array   $options
     * @return Context
     */
    protected function configureContext(Context $context, array $options)
    {
        if (isset($options['groups'])) {
            $context->setGroups($options['groups']);
        }
        if (isset($options['version'])) {
            $context->setVersion($options['version']);
        }

        return $context;
    }
}