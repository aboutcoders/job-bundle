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
 * @method static Status CANCELLING()
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
    const CANCELLING = 'CANCELLING';
    const CANCELLED  = 'CANCELLED';
    const ERROR      = 'ERROR';
    const SLEEPING   = 'SLEEPING';

    /**
     * @var array
     */
    private static $terminated_status_values = [self::PROCESSED, self::CANCELLED, self::ERROR];

    /**
     * @var array
     */
    private static $unterminated_status_values = [self::REQUESTED, self::PROCESSING, self::CANCELLING, self::SLEEPING];

    /**
     * @return array
     */
    public static function getTerminatedStatus()
    {
        return static::createFromValueArray(static::$terminated_status_values);
    }

    /**
     * @return array
     */
    public static function getUnterminatedStatus()
    {
        return static::createFromValueArray(static::$unterminated_status_values);
    }

    /**
     * @param Status $status
     * @return bool Whether the given status indicates that the job is terminated
     */
    public static function isTerminated(Status $status) {
        return in_array($status->getValue(), static::$terminated_status_values);
    }

    /**
     * @param array $valueArray
     * @return array
     */
    protected static function createFromValueArray(array $valueArray) {
        $result = [];
        foreach ($valueArray as $value) {
            $result[] = new Status($value);
        }

        return $result;
    }

    /**
     * @param Status $expected
     * @param Status $given
     * @return bool Whether the status of two instances is equal
     */
    public static function equals(Status $expected, Status $given) {
        return $expected->getValue() == $given->getValue();
    }
}