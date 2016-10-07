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

use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManager as BaseJobManager;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Entity manager for entities of type JobInterface
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class JobManager extends BaseJobManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var ScheduleManager
     */
    protected $scheduleManager;

    /**
     * Constructor.
     *
     * @param ObjectManager       $om
     * @param string              $class
     * @param ScheduleManager     $scheduleManager
     * @param SerializationHelper $serializationHelper
     */
    public function __construct(ObjectManager $om, $class, ScheduleManager $scheduleManager, SerializationHelper $serializationHelper)
    {
        $this->objectManager   = $om;
        $this->repository      = $om->getRepository($class);
        $this->scheduleManager = $scheduleManager;

        $metadata    = $om->getClassMetadata($class);
        $this->class = $metadata->getName();

        Job::setSerializationHelper($serializationHelper);
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param JobInterface $job
     * @param bool         $andFlush Whether to flush the changes (default true)
     * @return void
     */
    public function save(JobInterface $job, $andFlush = true)
    {
        $this->objectManager->persist($job);

        if($andFlush)
        {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(JobInterface $job)
    {
        $this->objectManager->remove($job);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function refresh(JobInterface $job)
    {
        $this->objectManager->refresh($job);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }
}