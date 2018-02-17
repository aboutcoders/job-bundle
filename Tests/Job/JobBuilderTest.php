<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job;

use Abc\Bundle\JobBundle\Job\JobBuilder;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Model\ScheduleInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobBuilderTest extends TestCase
{
    public function testCreate()
    {
        $type                = 'abc.mailer';
        $parameters          = [new Message()];
        $scheduleType1       = 'cron1';
        $scheduleExpression1 = 'expression1';
        $scheduleType2       = 'cron2';
        $scheduleExpression2 = 'expression2';

        $job = JobBuilder::create($type)
            ->setParameters($parameters)
            ->addSchedule($scheduleType1, $scheduleExpression1)
            ->addSchedule($scheduleType2, $scheduleExpression2)
            ->build();

        $this->assertInstanceOf(JobInterface::class, $job);
        $this->assertEquals($type, $job->getType());
        $this->assertEquals($parameters, $job->getParameters());

        $schedules = $job->getSchedules();
        $this->assertInstanceOf(ScheduleInterface::class, $schedules[0]);
        $this->assertInstanceOf(ScheduleInterface::class, $schedules[1]);
        $this->assertEquals($scheduleType1, $schedules[0]->getType());
        $this->assertEquals($scheduleExpression1, $schedules[0]->getExpression());
        $this->assertEquals($scheduleType2, $schedules[1]->getType());
        $this->assertEquals($scheduleExpression2, $schedules[1]->getExpression());
    }

    public function testBuildReturnsNewInstance()
    {
        $builder = JobBuilder::create('foobar');
        $job     = $builder->build();

        $this->assertNotSame($job, $builder->build());
    }
}