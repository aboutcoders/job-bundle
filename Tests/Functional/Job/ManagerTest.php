<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Job;

use Abc\Bundle\JobBundle\Job\ExceptionResponse;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\ProcessControl\Factory;
use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\ScheduleManagerInterface;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\ProcessControl\DoExitController;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestResponse;
use Abc\Bundle\SchedulerBundle\Model\Schedule;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ManagerTest extends DatabaseKernelTestCase
{
    public function testJobsCanLog()
    {
        $job = $this->getJobManager()->addJob('log', array('message'));

        $this->processJobs();

        $logs = $this->getJobManager()->getLogs($job->getTicket());

        $this->assertTrue($this->containsMessage('message', $logs));
    }

    public function testHandlesExceptionsThrownByJob()
    {
        $job = $this->getJobManager()->addJob('throw_exception', array('message', 100));

        $this->processJobs();

        $this->assertEquals(Status::ERROR(), $job->getStatus());
        $this->assertInstanceOf(ExceptionResponse::class, $job->getResponse());
        $this->assertEquals('message', $job->getResponse()->getMessage());
        $this->assertEquals(100, $job->getResponse()->getCode());
    }

    public function testJobCanSetResponse()
    {
        $expectedResponse = new TestResponse('foobar');
        $ticket           = $this->getJobManager()->addJob('set_response', array($expectedResponse));

        $this->processJobs();

        $response = $this->getJobManager()->get($ticket)->getResponse();

        $this->assertEquals($expectedResponse, $response);
    }

    public function testJobCanCreateSchedule()
    {
        $ticket = $this->getJobManager()->addJob('create_schedule', ['cron', '* * * * *']);

        $this->processJobs();

        $this->assertEquals(Status::SLEEPING(), $this->getJobManager()->get($ticket)->getStatus());

        $schedules = $this->getScheduleManager()->findSchedules();

        $this->assertCount(1, $schedules);

        /** @var Schedule $schedule */
        $schedule = $schedules[0];

        $this->assertEquals('cron', $schedule->getType());
        $this->assertEquals('* * * * *', $schedule->getExpression());
    }

    public function testJobCanUpdateSchedule()
    {
        $schedule = $this->getScheduleManager()->create('cron', '* * * * *');

        $this->getJobManager()->addJob('update_schedule', ['cron', '1 1 * * *'], $schedule);

        $this->runConsole("abc:scheduler:process", array("--iteration" => 1));

        $this->processJobs();

        $this->getEntityManager()->clear();

        $schedules = $this->getScheduleManager()->findSchedules();

        $this->assertCount(1, $schedules);

        /**
         * @var Schedule $schedule
         */
        $schedule = $schedules[0];

        $this->assertEquals('cron', $schedule->getType());
        $this->assertEquals('1 1 * * *', $schedule->getExpression());
    }

    public function testJobCanRemoveSchedule()
    {
        // create scheduled job
        $schedule = $this->getScheduleManager()->create('cron', '* * * * *');

        $ticket = $this->getJobManager()->addJob('remove_schedule', null, $schedule);

        $this->assertCount(1, $this->getScheduleManager()->findSchedules());
        $this->assertEquals(Status::REQUESTED(), $this->getJobManager()->get($ticket)->getStatus());

        // process schedules
        $this->runConsole("abc:scheduler:process", array("--iteration" => 1));

        $this->processJobs();

        $this->assertTrue($this->containsMessage('removed schedule', $this->getJobManager()->getLogs($ticket)));
        $this->assertEquals(Status::PROCESSED(), $this->getJobManager()->get($ticket)->getStatus());
        $this->assertEmpty($this->getScheduleManager()->findSchedules());
    }

    public function testCancelWithSchedule()
    {
        $schedule = new Schedule();
        $schedule->setExpression('* * * * *');
        $schedule->setType('cron');

        $ticket = $this->getJobManager()->addJob('schedule', array(1), $schedule);

        $this->processJobs();

        $this->getJobManager()->cancel($ticket);

        $this->assertEmpty($this->getJobManager()->getLogs($ticket));
        $this->assertEquals(Status::CANCELLED(), $this->getJobManager()->get($ticket)->getStatus());

        $this->assertEmpty($this->getScheduleManager()->findSchedules());
    }

    public function testScheduleIsDisabledIfJobThrowsException()
    {
        // create scheduled job that throws an exception
        $schedule = new Schedule();
        $schedule->setExpression('* * * * *');
        $schedule->setType('cron');

        $ticket = $this->getJobManager()->addJob('throw_exception', array('message', 100), $schedule);

        // process schedules
        $this->runConsole("abc:scheduler:process", array("--iteration" => 1));

        $this->processJobs();

        $this->assertEquals(Status::ERROR(), $this->getJobManager()->get($ticket)->getStatus());

        $schedules = $this->getScheduleManager()->findSchedules();

        $this->assertCount(0, $schedules);
    }

    public function testJobCanManageJobs()
    {
        $job = $this->getJobManager()->addJob('manage_job');

        $this->processJobs();

        $ticket = $job->getResponse();

        $logs = $this->getJobManager()->getLogs($ticket);

        $this->assertTrue($this->containsMessage('addedJob', $this->getJobManager()->getLogs($ticket)));
    }

    public function testJobCanBeCancelled()
    {
        /**
         * @var Factory $controllerFactory
         */
        $controllerFactory = $this->getContainer()->get('abc.job.controller_factory');
        $controllerFactory->addController(new DoExitController());

        $job = $this->getJobManager()->addJob('cancel');

        $this->processJobs();

        $this->assertContains('cancelled', $job->getResponse());
        $this->assertEquals(Status::CANCELLED(), $job->getStatus());
    }

    /**
     * @return ManagerInterface
     */
    protected function getJobManager()
    {
        return $this->getContainer()->get('abc.job.manager');
    }

    protected function processJobs()
    {
        /**
         * @var ConsumerInterface $consumer
         */
        $consumer = $this->getContainer()->get('abc.job.consumer');
        $consumer->consume('default', [
            'stop-when-empty' => true
        ]);
    }

    /**
     * @return ScheduleManagerInterface
     */
    protected function getScheduleManager()
    {
        return $this->getContainer()->get('abc.job.schedule_manager');
    }

    /**
     * @param string $key
     * @param array  $logs
     * @return bool
     */
    private function containsMessage($key, $logs)
    {
        foreach ($logs as $log) {
            if (false !== strpos($log['message'], $key)) {
                return true;
            }
        }

        return false;
    }
}