<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Doctrine;

use Abc\Bundle\JobBundle\Doctrine\JobManager;
use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Constraints\UuidValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobManagerTest extends DatabaseKernelTestCase
{
    /**
     * @var SerializationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializationHelper;

    /**
     * @var JobManager
     */
    private $subject;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->serializationHelper = $this->getMockBuilder(SerializationHelper::class)->disableOriginalConstructor()->getMock();

        $this->subject = new \Abc\Bundle\JobBundle\Entity\JobManager(
            $this->getEntityManager(),
            Job::class,
            $this->getScheduleManager(),
            $this->serializationHelper
        );
    }

    public function testIsExpectedInstance()
    {
        $this->assertInstanceOf(JobManager::class, $this->subject);
    }

    public function testSave()
    {
        /**
         * @var Status $status
         */
        $job = $this->subject->create('type');
        $job->setStatus(Status::REQUESTED());

        $this->subject->save($job);

        $this->assertCount(1, $this->subject->findAll());
        //Just in case save operation is delayed
        $now = new \DateTime();
        $now->add(new \DateInterval('P5M'));
        $this->assertLessThanOrEqual($now, $job->getCreatedAt());

        /**
         * @var ExecutionContext|\PHPUnit_Framework_MockObject_MockObject $context
         */
        $context   = $this->getMock(ExecutionContext::class, [], [], '', false);
        $validator = new UuidValidator();
        $validator->initialize($context);

        $context->expects($this->never())
            ->method('addViolation');

        $validator->validate($job->getTicket(), new Uuid());

        $this->getEntityManager()->clear();

        $job = $this->subject->findByTicket($job->getTicket());
        $this->assertInstanceOf(Status::class, $job->getStatus());
    }

    public function testHandlesParameterSerialization()
    {
        $type           = 'foobar';
        $parameters     = [['foo' => 'bar'], 'foobar'];
        $parameterTypes = ['array<String,String>', 'String'];

        // persist object with parameters being an array
        $this->serializationHelper->expects($this->once())
            ->method('serialize')
            ->with($parameters)
            ->willReturn('SerializedParameter');

        $job = $this->subject->create($type);
        $job->setStatus(Status::REQUESTED());
        $job->setParameters($parameters);
        $this->subject->save($job);

        // clear
        $this->getEntityManager()->clear();

        $this->serializationHelper->expects($this->once())
            ->method('deserializeParameters')
            ->with('SerializedParameter', $type)
            ->willReturn($parameters);

        $persistedJob = $this->subject->findByTicket($job->getTicket());

        $this->assertEquals($parameters, $persistedJob->getParameters());
    }

    public function testHandlesResponseSerialization()
    {
        $type     = 'foobar';
        $response = ['foo' => 'bar'];

        // persist object with parameters being an array
        $this->serializationHelper->expects($this->once())
            ->method('serialize')
            ->with($response)
            ->willReturn('SerializedResponse');

        $job = $this->subject->create($type);
        $job->setStatus(Status::REQUESTED());
        $job->setResponse($response);
        $this->subject->save($job);

        $this->getEntityManager()->clear();

        $this->serializationHelper->expects($this->once())
            ->method('deserializeResponse')
            ->with('SerializedResponse', $type)
            ->willReturn($response);

        $persistedJob = $this->subject->findByTicket($job->getTicket());

        $this->assertEquals($response, $persistedJob->getResponse());
    }

    public function testCascadesScheduleOperations()
    {
        $job = $this->subject->create('JobType', null, new Schedule('Type', 'Expression'));
        $job->setStatus(Status::REQUESTED());

        $this->subject->save($job);

        $this->getEntityManager()->clear();

        /**
         * @var Schedule[] $schedules
         */
        $schedules = $this->getScheduleManager()->findSchedules();
        $this->assertCount(1, $schedules);
        $this->assertEquals('Type', $schedules[0]->getType());
        $this->assertEquals('Expression', $schedules[0]->getExpression());

        // UPDATE
        $job = $this->subject->findByTicket($job->getTicket());

        /**
         * @var Schedule[] $schedules
         */
        $schedules = $job->getSchedules();
        $schedules[0]->setType('UpdatedType');
        $schedules[0]->setExpression('UpdatedExpression');

        $this->subject->save($job);

        $this->getEntityManager()->clear();

        /**
         * @var Schedule[] $schedules
         */
        $schedules = $this->getScheduleManager()->findSchedules();

        $this->assertCount(1, $schedules);
        $this->assertEquals('UpdatedType', $schedules[0]->getType());
        $this->assertEquals('UpdatedExpression', $schedules[0]->getExpression());

        // DELETE
        $job = $this->subject->findByTicket($job->getTicket());
        $job->removeSchedules();

        $this->subject->save($job);

        $this->assertEmpty($this->getScheduleManager()->findSchedules());
    }

    public function testCastsSchedulesBeforeSave()
    {
        $schedule = new \Abc\Bundle\SchedulerBundle\Model\Schedule();
        $schedule->setType('ScheduleType');
        $schedule->setExpression('ScheduleExpression');

        $job = $this->subject->create('JobType');
        $job->setStatus(Status::REQUESTED());
        $job->addSchedule($schedule);

        $this->subject->save($job);

        $this->assertCount(1, $this->subject->findAll());
    }

    /**
     * @return ScheduleManager
     */
    private function getScheduleManager()
    {
        return $this->getContainer()->get('abc.job.schedule_manager');
    }

    /**
     * @param string $jobType
     * @param array  $parameterTypes
     * @param string $responseType
     * @return JobType
     */
    private function setUpJobType($jobType, array $parameterTypes = [], $responseType = null)
    {
        $callable = function () {
        };
        $jobType  = new JobType('ServiceId', $jobType, $callable);
        $jobType->setParameterTypes($parameterTypes);
        $jobType->setResponseType($responseType);

        return $jobType;
    }
}