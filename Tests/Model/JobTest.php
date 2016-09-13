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

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testHasSchedule()
    {
        $job = new Job();

        $this->assertFalse($job->hasSchedules());

        $job->addSchedule(new Schedule());

        $this->assertTrue($job->hasSchedules());
    }

    public function testGetExecutionTime()
    {
        $subject      = new Job();
        $createdAt    = new \DateTime('2010-01-01 00:00:00');
        $terminatedAt = new \DateTime('2010-01-01 00:00:01');

        $subject->setCreatedAt($createdAt);
        $subject->setTerminatedAt($terminatedAt);

        $expectedProcessingTime = $subject->getTerminatedAt()->format('U') - $subject->getCreatedAt()->format('U');

        $this->assertEquals($expectedProcessingTime, $subject->getExecutionTime());
    }

    public function testClone()
    {
        $job = new Job;
        $job->setTicket('ticket');

        $clone = clone $job;

        $this->assertTrue($job !== $clone);
        $this->assertNotEquals($job->getTicket(), $clone->getTicket());
    }
}