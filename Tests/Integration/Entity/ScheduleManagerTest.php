<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Entity;

use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleManagerTest extends DatabaseKernelTestCase
{
    /** @var ScheduleManager */
    private $subject;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->subject = new ScheduleManager($this->getEntityManager(), Job::class);
    }

    public function testFindSchedules()
    {
        /**
         * @var ScheduleManager $scheduleManager
         */
        $scheduleManager = $this->getContainer()->get('abc.job.schedule_manager');

        /**
         * @var JobManagerInterface $jobManager
         */
        $jobManager = $this->getContainer()->get('abc.job.job_manager');

        $schedule1 = $scheduleManager->create();
        $schedule1->setType('cron');
        $schedule1->setExpression('foobar');
        $schedule1->setIsActive(true);

        $job1 = $jobManager->create('foo');
        $job1->setStatus(Status::REQUESTED());
        $job1->addSchedule($schedule1);
        $jobManager->save($job1);

        $schedule2 = $scheduleManager->create();
        $schedule2->setType('cron');
        $schedule2->setExpression('foobar');
        $schedule2->setIsActive(false);

        $job2 = $jobManager->create('foo');
        $job2->setStatus(Status::REQUESTED());
        $job2->addSchedule($schedule2);
        $jobManager->save($job2);

        $scheduleManager->save($schedule2);

        $schedules = $scheduleManager->findSchedules();

        $this->assertCount(1, $schedules);
        $this->assertContains($schedule1, $schedules);
    }
}