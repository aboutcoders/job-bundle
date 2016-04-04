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
    const REQUESTED  = 1;
    const PROCESSING = 2;
    const PROCESSED  = 3;
    const CANCELLED  = 4;
    const ERROR      = 5;
    const SLEEPING   = 6;

    /**
     * @var array
     */
    private static $internal_constants = null;

    /**
     * @var array
     */
    private static $terminated_status_values = array(self::PROCESSED, self::CANCELLED, self::ERROR);

    /**
     * @return string
     */
    public function getName()
    {
        return array_search($this->getValue(), $this->getInternalConstants());
    }

    /**
     * @return array
     */
    public static function getTerminatedStatusValues()
    {
        return self::$terminated_status_values;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    private static function getInternalConstants()
    {
        if(self::$internal_constants == null)
        {
            $reflection      = new \ReflectionClass(get_class());
            self::$internal_constants = $reflection->getConstants();
        }

        return self::$internal_constants;
    }
}