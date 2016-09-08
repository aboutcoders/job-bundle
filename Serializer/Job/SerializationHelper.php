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

use Abc\Bundle\JobBundle\Job\JobParameterArray;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Serializer\SerializerInterface;

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
     * Serializes data.
     *
     * @param mixed $data
     * @return string The serialized parameters
     */
    public function serialize($data)
    {
        return $this->serializer->serialize($data, 'json');
    }

    /**
     * Deserializes the job parameters
     *
     * @param string $data    The serialized parameters
     * @param string $jobType The job type
     * @return array The deserialized parameters
     * @throws \InvalidArgumentException If no serializable parameters are defined for the job type
     */
    public function deserializeParameters($data, $jobType)
    {
        $types = $this->registry->get($jobType)->getSerializableParameterTypes();

        if (count($types) == null) {
            throw new \InvalidArgumentException(sprintf('No serializable parameters defined for job "%s"', $jobType));
        }

        $type = sprintf(JobParameterArray::class . '<%s>', implode($types, ','));

        return $this->serializer->deserialize($data, $type, 'json');
    }

    /**
     * Deserializes a job response
     *
     * @param string $data
     * @param string $jobType The job type
     * @return mixed
     */
    public function deserializeResponse($data, $jobType)
    {
        $type = $this->registry->get($jobType)->getResponseType();

        return $this->serializer->deserialize($data, $type, 'json');
    }
}