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

use Abc\Bundle\JobBundle\Model\ScheduleInterface;
use Abc\Bundle\JobBundle\Model\ScheduleManagerInterface;
use Abc\Bundle\SchedulerBundle\Doctrine\ScheduleManager as BaseScheduleManager;

/**
 * Doctrine EntityManager for entities of type Abc\Bundle\JobBundle\Model\ScheduleInterface
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleManager extends BaseScheduleManager implements ScheduleManagerInterface
{
    /**
     * {@inheritDoc}
     */
    public function create($type = null, $expression = null, $active = true)
    {
        $class = $this->getClass();

        /** @var ScheduleInterface $schedule */
        $schedule = new $class;
        $schedule->setType($type);
        $schedule->setExpression($expression);
        $schedule->setIsActive($active == null ? true : $active);

        return $schedule;
    }

    /**
     * {@inheritDoc}
     */
    public function findSchedules($limit = null, $offset = null)
    {
        return $this->repository->findBy(array('isActive' => true), array(), $limit, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ScheduleInterface $schedule)
    {
        $this->objectManager->remove($schedule);
        $this->objectManager->flush();
    }
}