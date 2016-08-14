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

use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Entity\Schedule;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSchedule()
    {
        $subject = new Job();
        $schedule = $subject->createSchedule('foo', 'bar');

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertEquals('foo', $schedule->getType());
        $this->assertEquals('bar', $schedule->getExpression());
    }

    public function testClone()
    {
        $job = new Job;
        $job->setTicket('ticket');

        $clone = clone $job;

        $this->assertTrue($job !== $clone);
    }
}