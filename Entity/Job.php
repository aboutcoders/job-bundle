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

use Abc\Bundle\JobBundle\Doctrine\Job as BaseJob;
use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Job extends BaseJob
{
    /**
     * {@inheritdoc}
     */
    public function createSchedule($type, $expression)
    {
        return new Schedule($type, $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function addSchedule(ScheduleInterface $schedule)
    {
        if(!$schedule instanceof Schedule)
        {
            $schedule = new Schedule($schedule->getType(), $schedule->getExpression());
        }

        $schedule->setJob($this);

        parent::addSchedule($schedule);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSchedule(ScheduleInterface $schedule)
    {
        if($schedule instanceof Schedule)
        {
            $schedule->setJob(null);
        }

        parent::removeSchedule($schedule);
    }
}