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
abstract class JobManager implements JobManagerInterface
{
    /**
     * {@inheritDoc}
     */
    public function create($type = null, $parameters = null, BaseScheduleInterface $schedule = null)
    {
        $class = $this->getClass();

        /** @var JobInterface $job */
        $job   = new $class;

        $job->setType($type);
        $job->setParameters($parameters);

        if(!is_null($schedule))
        {
            $job->addSchedule($schedule);
        }

        return $job;
    }

    /**
     * {@inheritDoc}
     */
    public function findByTicket($ticket)
    {
        $jobs = $this->findBy(array('ticket' => $ticket));

        return count($jobs) > 0 ? $jobs[0] : null;
    }
}