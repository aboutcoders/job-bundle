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

use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Model\ScheduleInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleBuilder
{
    /**
     * @param $type
     * @param $expression
     * @return ScheduleInterface
     */
    public static function create($type, $expression)
    {
        return new Schedule($type, $expression);
    }
}