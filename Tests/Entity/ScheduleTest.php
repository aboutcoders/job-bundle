<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Entity;

use Abc\Bundle\JobBundle\Entity\Schedule;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleTest extends TestCase
{
    public function testClone()
    {
        $schedule = new Schedule();
        $schedule->setCreatedAt(new \DateTime);
        $schedule->setUpdatedAt(new \DateTime);
        $schedule->setScheduledAt(new \DateTime);

        $ref = new \ReflectionClass($schedule);
        $property = $ref->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($schedule, 1);

        $clone = clone $schedule;

        $this->assertNull($clone->getId());
    }
}