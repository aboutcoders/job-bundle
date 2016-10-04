<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Serializer;

use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobSerializationTest extends KernelTestCase
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        static::bootKernel();
        $this->serializer = static::$kernel->getContainer()->get('abc.job.serializer');
    }

    /**
     * @param Job $job
     * @dataProvider provideJobs
     */
    public function testSerialization($job)
    {
        $data = $this->serializer->serialize($job, 'json');

        $deserializedJob = $this->serializer->deserialize($data, Job::class, 'json');

        $this->assertEquals($job, $deserializedJob);
    }

    /**
     * @param Job    $expectedJob
     * @param string $data
     * @param array  $groups
     * @dataProvider provideSerializedJob
     */
    public function testDeserialization($expectedJob, $data, array $groups = [])
    {
        $context = null;
        if (count($groups) > 0) {
            $context = new DeserializationContext();
            $context->setGroups($groups);
        }

        $job = $this->serializer->deserialize($data, Job::class, 'json', $context);

        $this->assertEquals($expectedJob, $job);
    }

    /**
     * @return array
     */
    public static function provideJobs()
    {
        return [
            [self::createJob('JobTicket', 'abc.mailer', Status::PROCESSING(), 12345)],
            [self::createJob('JobTicket', 'abc.mailer', Status::PROCESSING(), 12345, [new Message('to@domain.tld', 'from@domain.tld', 'Message Subject', 'Message Body')])],
            [self::createJob('JobTicket', 'abc.mailer', Status::PROCESSING(), 12345, null, [self::createSchedule()])],
        ];
    }

    public static function provideSerializedJob()
    {
        return [
            [
                self::createJob('JobTicket', 'abc.mailer', Status::PROCESSING(), 12345),
                '{"ticket":"JobTicket","type":"abc.mailer","status":"PROCESSING","processing_time":12345}'
            ],
            [
                self::createJob(null, 'abc.mailer', null, null, [new Message('to@domain.tld', 'from@domain.tld', 'Message Subject', 'Message Body')]),
                '{"type":"abc.mailer","parameters":[{"to":"to@domain.tld","from":"from@domain.tld","subject":"Message Subject","message":"Message Body"}]}',
            ],
            [
                self::createJob(null, 'abc.mailer', null, null, null, [self::createSchedule('cron', '* * * * *')]),
                '{"type":"abc.mailer","schedules":[{"type":"cron","expression":"* * * * *","is_active":true}]}',
                //['create'] // not sure yet if we want to use serialization groups
            ],
        ];
    }

    /**
     * @return Job
     */
    public static function createJob($ticket = null, $type = null, $status = null, $processingTime = null, $parameters = null, array $schedules = array())
    {
        $job = new Job();
        $job->setTicket($ticket);
        $job->setType($type);
        $job->setParameters($parameters);
        $job->setProcessingTime($processingTime);
        foreach ($schedules as $schedule) {
            $job->addSchedule($schedule);
        }

        if ($status != null) {
            $job->setStatus($status);
        }

        return $job;
    }

    /**
     * @param string $type
     * @param string $schedule
     * @return Schedule
     */
    public static function createSchedule($type = null, $schedule = null)
    {
        return new Schedule($type, $schedule);
    }
}