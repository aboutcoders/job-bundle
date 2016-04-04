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

use Abc\Bundle\SchedulerBundle\Model\ScheduleManagerInterface as BaseScheduleManagerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ScheduleManagerInterface extends BaseScheduleManagerInterface
{
    /**
     * @param string|null $type
     * @param string|null $expression
     * @param bool $active true by default
     * @return ScheduleInterface
     */
    public function create($type = null, $expression = null, $active = true);

    /**
     * @param ScheduleInterface $schedule
     * @return void
     */
    public function delete(ScheduleInterface $schedule);
}