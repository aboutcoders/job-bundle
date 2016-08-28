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

use Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata;
use Abc\Bundle\JobBundle\Job\Queue\QueueConfigInterface;
use Metadata\MetadataFactoryInterface;

/**
 * Job definition registry.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeRegistry
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var QueueConfigInterface
     */
    private $queueConfig;

    /**
     * @var array
     */
    private $types = [];

    /**
     * @param MetadataFactoryInterface $metadataFactory
     * @param QueueConfigInterface     $queueConfig
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, QueueConfigInterface $queueConfig)
    {
        $this->metadataFactory = $metadataFactory;
        $this->queueConfig = $queueConfig;
    }

    /**
     * @return JobTypeInterface[]
     */
    public function all()
    {
        return $this->types;
    }

    /**
     * @param string $type The job type
     * @return bool Whether a definition for the given type exists
     */
    public function has($type)
    {
        return array_key_exists($type, $this->types);
    }

    /**
     * @param JobTypeInterface $jobType
     * @param bool             $loadClassMetadata Whether to load class meta data of the job
     */
    public function register(JobTypeInterface $jobType, $loadClassMetadata = false)
    {
        if ($loadClassMetadata) {
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass($jobType->getClass())->getRootClassMetadata();

            $jobType->setParameterTypes($classMetadata->getMethodArgumentTypes($jobType->getMethod()));
            $jobType->setResponseType($classMetadata->getMethodReturnType($jobType->getMethod()));
        }

        $jobType->setQueue($this->queueConfig->getQueue($jobType->getName()));

        $this->types[$jobType->getName()] = $jobType;
    }

    /**
     * @param string $type The job type
     * @return JobTypeInterface
     * @throws JobTypeNotFoundException If a definition with the given type does not exist
     */
    public function get($type)
    {
        if (!isset($this->types[$type])) {
            throw new JobTypeNotFoundException($type);
        }

        return $this->types[$type];
    }

    /**
     * @return string[] An array containing all registered type keys
     */
    public function getTypeChoices()
    {
        return array_keys($this->types);
    }
}