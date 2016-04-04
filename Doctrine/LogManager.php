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

use Abc\Bundle\JobBundle\Model\LogInterface;
use Abc\Bundle\JobBundle\Model\LogManager as BaseLogManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Doctrine EntityManager for entities of type Abc\Bundle\JobBundle\Model\ScheduleInterface
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class LogManager extends BaseLogManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param ObjectManager    $om
     * @param string           $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager   = $om;
        $this->repository      = $om->getRepository($class);

        $metadata    = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param LogInterface $log
     * @param bool      $andFlush Whether to flush the changes (default true)
     */
    public function save(LogInterface $log, $andFlush = true)
    {
        $this->objectManager->persist($log);

        if($andFlush)
        {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->repository->findAll();
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
    public function findByChannel($channel)
    {
        return $this->repository->findBy(array('channel' => $channel));
    }

    /**
     * {@inheritDoc}
     */
    public function delete(LogInterface $log)
    {
        $this->objectManager->remove($log);
        $this->objectManager->flush();
    }
}