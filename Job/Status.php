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

use MyCLabs\Enum\Enum;

/**
 * Status of a job.
 *
 * @method static Status REQUESTED()
 * @method static Status PROCESSING()
 * @method static Status PROCESSED()
 * @method static Status CANCELLED()
 * @method static Status ERROR()
 * @method static Status SLEEPING()
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Status extends Enum
{
    const REQUESTED  = 'REQUESTED';
    const PROCESSING = 'PROCESSING';
    const PROCESSED  = 'PROCESSED';
    const CANCELLED  = 'CANCELLED';
    const ERROR      = 'ERROR';
    const SLEEPING   = 'SLEEPING';

    /**
     * @var array
     */
    private static $terminated_status_values = array(self::PROCESSED, self::CANCELLED, self::ERROR);

    /**
     * @return array
     */
    public static function getTerminatedStatusValues()
    {
        return self::$terminated_status_values;
    }
}