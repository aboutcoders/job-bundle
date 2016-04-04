<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Model;

use Abc\Bundle\JobBundle\Model\Schedule;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{

    public function testClone()
    {
        $schedule = new Schedule();
        $schedule->setCreatedAt(new \DateTime);
        $schedule->setUpdatedAt(new \DateTime);
        $schedule->setScheduledAt(new \DateTime);

        $clone = clone $schedule;

        $this->assertNull($clone->getScheduledAt());
        $this->assertNull($clone->getUpdatedAt());
        $this->assertNull($clone->getCreatedAt());
    }
}