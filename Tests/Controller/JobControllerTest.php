<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Controller;

use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Tests\DatabaseWebTestCase;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobControllerTest extends DatabaseWebTestCase
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->manager    = $this->getMock(ManagerInterface::class);
        $this->serializer = SerializerBuilder::create()->build();

        // setup serializer (otherwise ->equalTo will)
        if (!static::$initialized) {
            /** @var SerializerInterface $serializer */
            $serializer       = static::$kernel->getContainer()->get('jms_serializer');
            $this->serializer = $serializer;
            Job::setSerializer($this->serializer);
            static::$initialized = true;
        }
    }

    /**
     * @param array $parameters
     * @param int   $expectedNumOfItems
     * @param int   $expectedTotalCount
     * @dataProvider provideCgetData
     */
    public function testCgetAction($parameters = null, $expectedNumOfItems, $expectedTotalCount)
    {
        $client = static::createClient();

        /** @var JobManagerInterface $manager */
        $manager = static::$kernel->getContainer()->get('abc.job.job_manager');

        $job1 = $manager->create('foo');
        $job1->setStatus(Status::REQUESTED());
        $manager->save($job1);

        $job2 = $manager->create('bar');
        $job2->setStatus(Status::PROCESSING());
        $manager->save($job2);

        $job3 = $manager->create('foobar');
        $job3->setStatus(Status::PROCESSED());
        $job3->setTerminatedAt(new \DateTime);
        $manager->save($job3);

        $url = '/api/jobs';

        if (!is_null($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        $client->request(
            'GET',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            null,
            'json'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        /** @var JobList $list */
        // we need the initialized serializer here, since we have custom handlers initialized
        $serializer = static::$kernel->getContainer()->get('jms_serializer');
        $list       = $serializer->deserialize($data, JobList::class, 'json');

        $this->assertCount($expectedNumOfItems, $list->getItems());
        $this->assertEquals($expectedTotalCount, $list->getTotalCount());
    }

    public function testCgetActionValidatesStatus()
    {
        $url = '/api/jobs?' . http_build_query(['criteria' => ['status' => 'foo']]);
        $client = static::createClient();

        $client->request(
            'GET',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            null,
            'json'
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testGetAction()
    {

        $job = new Job();
        $job->setTicket('12345');
        $job->setStatus(Status::PROCESSING());

        $client = static::createClient();

        // get the (initialized) serializer before it is destroyed by this mockManager() call, otherwise we do not have custom handlers initialized
        $serializer = static::$kernel->getContainer()->get('jms_serializer');

        $this->mockManager();

        $this->manager->expects($this->once())
            ->method('get')
            ->with('12345')
            ->willReturn($job);

        $client->request('GET', '/api/jobs/12345');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $returnedJob = $serializer->deserialize($client->getResponse()->getContent(), \Abc\Bundle\JobBundle\Model\Job::class, 'json');

        $this->assertEquals($job->getTicket(), $returnedJob->getTicket());
    }

    public function testGetActionReturns404()
    {

        $client = static::createClient();

        $this->mockManager();

        $this->manager->expects($this->once())
            ->method('get')
            ->willThrowException(new TicketNotFoundException('12345'));

        $client->request('GET', '/api/jobs/12345');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @param $parameters
     * @dataProvider providePostData
     */
    public function testPostAction($parameters, $expectedStatusCode)
    {
        $job = $this->buildJobFromArray($parameters);

        if ($expectedStatusCode >= 200 && $expectedStatusCode < 400) {
            $this->manager->expects($this->once())
                ->method('add')
                ->with($this->equalTo($job))
                ->willReturnCallback(function () use ($job) {
                    $job = clone $job;
                    $job->setTicket('JobTicket');

                    return $job;
                });
        }

        $client = static::createClient();

        $this->mockManager();

        $client->request(
            'POST',
            '/api/jobs',
            $parameters,
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            null,
            'json'
        );

        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);

        if ($expectedStatusCode >= 200 && $expectedStatusCode < 400) {
            $this->assertEquals('JobTicket', $data['ticket']);
        }
    }

    public function testGetLogsAction()
    {
        $job = new Job();
        $job->setTicket('12345');

        $client = static::createClient();

        $this->mockManager();

        $this->manager->expects($this->once())
            ->method('getJobLogs')
            ->with($job->getTicket())
            ->willReturn('LogMessage');

        $client->request('get', '/api/jobs/12345/logs');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        $this->assertEquals('"LogMessage"', $data);
    }

    public function testCancelAction()
    {
        $job = new Job();
        $job->setTicket('12345');
        $job->setStatus(Status::CANCELLED());

        $client = static::createClient();

        // get the (initialized) serializer before it is destroyed by this mockManager() call, otherwise we do not have custom handlers initialized
        $serializer = static::$kernel->getContainer()->get('jms_serializer');

        $this->mockManager();

        $this->manager->expects($this->once())
            ->method('cancelJob')
            ->with($job->getTicket())
            ->willReturn($job);

        $client->request('POST', '/api/jobs/12345/cancel');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        /** @var JobInterface $deserializedObject */
        $deserializedObject = $serializer->deserialize($data, Job::class, 'json');

        $this->assertEquals($job->getStatus(), $deserializedObject->getStatus());
    }

    public static function providePostData()
    {
        return [
            [
                [
                    'type'       => 'mailer',
                    'parameters' => [
                        'to'      => 'to@domain.tld',
                        'from'    => 'from@domain.tld',
                        'message' => 'message body',
                        'subject' => 'subject',
                    ]
                ],
                200
            ],
            [
                [
                    'type'       => 'mailer',
                    'parameters' => [
                        'to'      => 'to@domain.tld',
                        'from'    => 'from@domain.tld',
                        'message' => 'message body',
                        'subject' => 'subject',
                    ],
                    'schedules'  => [
                        [
                            'type'       => 'cron',
                            'expression' => '* * * * *',
                        ]
                    ]
                ],
                200
            ],
            [
                [
                    'type'       => 'mailer',
                    'parameters' => [
                        'to'      => 'foobar',
                        'from'    => 'from@domain.tld',
                        'message' => 'message body',
                        'subject' => 'subject',
                    ]
                ],
                400
            ],
            [
                [
                    'type'       => 'parameterless'
                ],
                200
            ],
            [
                [
                    'type'       => 'parameterless',
                    'parameters' => null
                ],
                200
            ],
            [
                [
                    'type' => 'undefined'
                ],
                404
            ]
        ];
    }

    /**
     * @return array An array [parameters, expectedNumberOfItems, expectedTotalCount]
     */
    public function provideCgetData()
    {
        return [
            [
                null,
                3,
                3
            ],
            [
                ['page' => 1, 'sortCol' => 'type', 'sortDir' => 'ASC'],
                3,
                3
            ],
            [
                ['criteria' => ['type' => 'foo']],
                1,
                1
            ],
            [
                ['criteria' => ['type' => 'foo', 'status' => 'REQUESTED']],
                1,
                1
            ],
            [
                ['page' => 3, 'sortCol' => 'type', 'sortDir' => 'DESC', 'limit' => 1],
                1,
                3
            ]
        ];
    }

    /**
     * Injects a mock object for the service abc.job.manager
     *
     * @see http://blog.lyrixx.info/2013/04/12/symfony2-how-to-mock-services-during-functional-tests.html
     */
    private function mockManager()
    {
        $manager = $this->manager;

        /**
         * @ignore
         */
        static::$kernel->setKernelModifier(
            function (KernelInterface $kernel) use ($manager) {
                $kernel->getContainer()->set('abc.job.manager', $manager);
            }
        );
    }

    /**
     * @param array $parameters
     * @return Job
     */
    private function buildJobFromArray($parameters)
    {
        $job = new Job();

        $job->setType(isset($parameters['type']) ? $parameters['type'] : null);

        if (isset($parameters['parameters'])) {
            $message = new Message(
                $parameters['parameters']['to'],
                $parameters['parameters']['from'],
                $parameters['parameters']['subject'],
                $parameters['parameters']['message']
            );

            $job->setParameters([$message]);
        }

        if (isset($parameters['schedules'])) {
            foreach ($parameters['schedules'] as $scheduleParameters) {
                $schedule = $job->createSchedule($scheduleParameters['type'], $scheduleParameters['expression']);
                $job->addSchedule($schedule);
            }
        }

        return $job;
    }
}