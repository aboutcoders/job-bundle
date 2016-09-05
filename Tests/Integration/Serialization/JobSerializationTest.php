<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Serialization;

use Abc\Bundle\EnumSerializerBundle\Serializer\Handler\EnumHandler;
use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Serializer\EventDispatcher\JobDeserializationSubscriber;
use Abc\Bundle\JobBundle\Serializer\Handler\JobParameterArrayHandler;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobSerializationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(JobTypeRegistry::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->setUpSerializer($this->registry);
    }

    /**
     * @param Job $job
     * @dataProvider provideJobs
     */
    public function testSerialization($job)
    {
        $this->setUpRegistry($job);

        $data = $this->serializer->serialize($job, 'json');

        $deserializedJob = $this->serializer->deserialize($data, Job::class, 'json');

        $this->assertEquals($job, $deserializedJob);
    }

    /**
     * @param Job    $expectedJob
     * @param string $data
     * @dataProvider provideSerializedJob
     */
    public function testDeserialization($expectedJob, $data)
    {
        $this->assertEquals($expectedJob, $this->serializer->deserialize($data, Job::class, 'json'));
    }

    /**
     * @return array
     */
    public function provideJobs()
    {
        $job = $this->createJob();

        $jobWithSchedule = $this->createJob();
        $jobWithSchedule->addSchedule($this->createSchedule());

        $jobWithParameters = $this->createJob();
        $jobWithParameters->setParameters([$this->createMessage()]);

        return [
            [$job],
            [$jobWithSchedule],
            [$jobWithParameters],
        ];
    }

    public function provideSerializedJob()
    {
        return [
            [$this->createJob(), '{"ticket":"JobTicket","type":"abc.mailer","status":"PROCESSING","processing_time":0.5}']
        ];
    }

    /**
     * @return array
     */
    public function getJobArray()
    {
        return [
            'ticket'          => 'JobTicket',
            'type'            => 'abc.mailer',
            'status'          => 'PROCESSING',
            'processing_time' => 0.5,
            'parameters'      => [
                ['to'      => 'to@domain.tld',
                 'from'    => 'from@domain.tld',
                 'message' => 'message body',
                 'subject' => 'subject'
                ]
            ],
            'schedules'       => [
                [
                    'type'       => 'cron',
                    'expression' => '* * * * *'
                ]
            ]
        ];
    }

    /**
     * @return Job
     */
    public static function createJob()
    {
        $job = new Job();
        $job->setTicket('JobTicket');
        $job->setType('abc.mailer');
        $job->setStatus(Status::PROCESSING());
        $job->setProcessingTime((double)0.5);

        return $job;
    }

    /**
     * @param string $type
     * @param string $schedule
     * @return Schedule
     */
    public function createSchedule($type = 'cron', $schedule = '* * * * *')
    {
        return new Schedule($type, $schedule);
    }

    /**
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $message
     * @return Message
     */
    public function createMessage($to = 'to@domain.tld', $from = 'from@domain.tld', $subject = 'Message Subject', $message = 'Message Body')
    {
        return new Message($to, $from, $subject, $message);
    }

    /**
     * @param JobTypeRegistry $registry
     */
    private function setUpSerializer(JobTypeRegistry $registry)
    {
        EnumHandler::register(Status::class);
        $enumHandler = new EnumHandler();

        $this->serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistry $handlerRegistry) use ($enumHandler) {
                $handlerRegistry->registerSubscribingHandler(new JobParameterArrayHandler());
                $handlerRegistry->registerSubscribingHandler($enumHandler);
            })
            ->configureListeners(function (EventDispatcher $dispatcher) use ($registry) {
                $dispatcher->addSubscriber(new JobDeserializationSubscriber($registry));
            })
            ->build();
    }

    /**
     * @param Job $job
     * @return void
     */
    private function setUpRegistry(Job $job)
    {
        if ($job->getParameters() != null && is_array($job->getParameters()) && count($job->getParameters()) > 0) {
            $parameterTypes = [];
            foreach ($job->getParameters() as $parameter) {
                $parameterTypes[] = (is_object($parameter)) ? get_class($parameter) : gettype($parameter);
            }

            $jobType = $this->getMockBuilder(JobType::class)->disableOriginalConstructor()->getMock();

            $jobType->expects($this->once())
                ->method('getParameterTypes')
                ->willReturn($parameterTypes);

            $this->registry->expects($this->once())
                ->method('get')
                ->with($job->getType())
                ->willReturn($jobType);
        }
    }
}