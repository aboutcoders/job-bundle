<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Model;

use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface as BaseScheduleInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface JobManagerInterface
{
    /**
     * @param string                                                   $type
     * @param string|null                                              $parameters
     * @param BaseScheduleInterface|null $schedule
     * @return JobInterface
     */
    public function create($type = null, $parameters = null, BaseScheduleInterface $schedule = null);

    /**
     * @param JobInterface $job
     * @return void
     */
    public function delete(JobInterface $job);

    /**
     * @param JobInterface $job
     * @return void
     */
    public function refresh(JobInterface $job);

    /**
     * @param JobInterface $job
     * @return void
     */
    public function save(JobInterface $job);

    /**
     * Returns a collection with all instances.
     *
     * @return JobInterface[]
     */
    public function findAll();

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     * @return JobInterface[]
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Returns count of matching rows for that criteria
     *
     * @param array $criteria
     *
     * @return integer
     */
    public function findByCount(array $criteria);

    /**
     * @param string $ticket
     * @return JobInterface|null
     */
    public function findByTicket($ticket);

    /**
     * @param array $tickets
     * @return JobInterface[]
     */
    public function findByTickets(array $tickets);

    /**
     * @param array $types
     * @return JobInterface[]
     */
    public function findByTypes(array $types);

    /**
     * @param int   $days The number of days since the jobs terminated
     * @param array $tickets Optional, if specified only jobs of the given types are returned
     * @return JobInterface[]
     */
    public function findByAgeAndTypes($days, array $tickets = array());

    /**
     * Returns the jobs's fully qualified class name.
     *
     * @return string
     */
    public function getClass();
}