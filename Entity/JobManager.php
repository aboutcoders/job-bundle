<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Entity;

use Abc\Bundle\JobBundle\Doctrine\JobManager as BaseJobManager;
use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobManager extends BaseJobManager
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager       $em
     * @param string              $class
     * @param ScheduleManager     $scheduleManager
     * @param SerializerInterface $serializer
     * @param JobTypeRegistry     $registry
     */
    public function __construct(EntityManager $em, $class, ScheduleManager $scheduleManager, SerializerInterface $serializer, JobTypeRegistry $registry)
    {
        parent::__construct($em, $class, $scheduleManager, $serializer, $registry);

        $this->em = $em;
    }

    /**
     * @inheritdoc
     */
    public function findByCount(array $criteria)
    {
        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder->select(sprintf('COUNT(%s)', $this->getAlias()));

        $queryBuilder = $this->buildMatchingQueryForCriteria($queryBuilder, $criteria);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByTickets(array $tickets)
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->select();

        $queryBuilder = $this->buildMatchingQueryForCriteria($queryBuilder, array('ticket' => $tickets));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByTypes(array $types)
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->select();

        $queryBuilder = $this->buildMatchingQueryForCriteria($queryBuilder, array('type' => $types));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByAgeAndTypes($days, array $types = array())
    {
        $qb = $this->em->createQueryBuilder();

        $terminationDate = new \DateTime;
        $terminationDate->setTimestamp(strtotime(sprintf('today - %d days', $days)));

        $condition = $qb->expr()->lte('job.terminatedAt', ':terminatedAt');
        $parameters = array(':terminatedAt' => $terminationDate);

        if(is_array($types) && count($types) > 0)
        {
            $parameters[':types'] = $types;
            $condition = $qb->expr()->andX($condition, $qb->expr()->in('job.type', ':types'));
        }

        $qb->select('job')
            ->from($this->getClass(), 'job')
            ->where($condition);

        $qb->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param array                      $criteria
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildMatchingQueryForCriteria($queryBuilder, array $criteria)
    {
        foreach ($criteria as $key => $value) {

            $operator = ' = :%s';

            if (is_array($value)) {

                if (count($value) == 1 && array_keys($value)[0] === '$match') {

                    $firstValue = reset($value);

                    //Only like is supported here at the moment
                    $operator = ' LIKE :%s';
                    $value    = '%' . $firstValue . '%';

                } else {
                    $operator = ' IN (:%s)';
                }
            }

            $queryBuilder->andWhere($this->getAlias() . '.' . $key . sprintf($operator, $key))
                ->setParameter($key, $value);
        }

        return $queryBuilder;
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'job';
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->em->getRepository($this->getClass())->createQueryBuilder($this->getAlias());
    }
}