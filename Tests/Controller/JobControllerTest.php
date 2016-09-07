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

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Test\DatabaseWebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobControllerTest extends DatabaseWebTestCase
{
    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->manager       = $this->getMock(ManagerInterface::class);
        $this->entityManager = $this->getMock(JobManagerInterface::class);
    }

    /**
     * @dataProvider provideListData
     * @param array $parameters
     */
    public function testListAction($parameters)
    {
        $url = '/api/jobs';
        if (!is_null($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        // expected parameters passed to the manager
        $criteria = isset($parameters['criteria']) ? $parameters['criteria'] : [];
        $sortCol  = isset($parameters['sortCol']) ? $parameters['sortCol'] : 'createdAt';
        $sortDir  = isset($parameters['sortDir']) ? $parameters['sortDir'] : 'DESC';
        $orderBy  = [$sortCol => $sortDir];
        $limit    = isset($parameters['limit']) ? $parameters['limit'] : 10;
        $page     = isset($parameters['page']) ? $parameters['page'] : 1;
        $page     = (int)$page - 1;
        $offset   = ($page > 0) ? ($page) * $limit : 0;

        $job = new Job();
        $job->setTicket('JobTicket');

        $this->entityManager->expects($this->once())
            ->method('findBy')
            ->with($criteria, $orderBy, $limit, $offset)
            ->willReturn([$job]);

        $this->entityManager->expects($this->once())
            ->method('findByCount')
            ->with($criteria)
            ->willReturn(5);

        $client = static::createClient();

        $this->mockServices([
            'abc.job.job_manager' => $this->entityManager
        ]);

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

        /**
         * @var JobList $deserializedList
         */
        $deserializedList = $this->getContainer()->get('abc.job.serializer')->deserialize($data, JobList::class, 'json');
        $items            = $deserializedList->getItems();

        /**
         * @var Job $deserializedEntity ;
         */
        $deserializedEntity = $items[0];

        $this->assertEquals(5, $deserializedList->getTotalCount());
        $this->assertEquals($job->getTicket(), $deserializedEntity->getTicket());
    }

    public function testListActionWithInvalidCriteria()
    {
        $parameters = ['criteria' => 'foobar'];

        $url = '/api/jobs';
        if (!is_null($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        $this->entityManager->expects($this->never())
            ->method('findBy');

        $this->entityManager->expects($this->never())
            ->method('findByCount');

        $client = static::createClient();

        $this->mockServices([
            'abc.job.job_manager' => $this->entityManager
        ]);

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

    /**
     * @return array An array of GET parameters
     */
    public function provideListData()
    {
        return [
            [
                null
            ],
            [
                ['page' => 1, 'sortCol' => 'type', 'sortDir' => 'ASC', 'limit' => 2]
            ],
            [
                ['criteria' => ['name' => 'foobar']]
            ],
            [
                ['criteria' => ['status' => 'PROCESSING']]
            ]
        ];
    }

    public function testGetAction()
    {
        $job = new Job();
        $job->setTicket('12345');
        $job->setStatus(Status::PROCESSING());

        $client = static::createClient();

        // get the (initialized) serializer before it is destroyed by this mockManager() call, otherwise we do not have custom handlers initialized
        $serializer = static::$kernel->getContainer()->get('abc.job.serializer');

        $this->mockServices(['abc.job.manager' => $this->manager]);

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

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('get')
            ->willThrowException(new TicketNotFoundException('12345'));

        $client->request('GET', '/api/jobs/12345');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @param $parameters
     * @dataProvider provideValidPostParameters
     */
    public function testAddAction($parameters)
    {
        $url = '/api/jobs';
        $job = $this->buildJobFromArray($parameters);

        $this->manager->expects($this->once())
            ->method('add')
            ->with()
            ->willReturnCallback(function () use ($job) {
                $job = clone $job;
                $job->setTicket('JobTicket');

                return $job;
            });

        $client = static::createClient();

        $this->mockServices([
            'abc.job.manager' => $this->manager
        ]);

        $client->request('POST', $url, $parameters, [], ['CONTENT_TYPE' => 'application/json'], null, 'json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @param        $parameters
     * @dataProvider provideValidPostParameters
     */
    public function testPutAction($parameters)
    {
        $url = '/api/jobs';
        $job = $this->buildJobFromArray($parameters);

        $this->manager->expects($this->once())
            ->method('update')
            ->with()
            ->willReturnCallback(function () use ($job) {
                $job = clone $job;
                $job->setTicket('JobTicket');

                return $job;
            });

        $client = static::createClient();

        $this->mockServices([
            'abc.job.manager' => $this->manager
        ]);

        $client->request('PUT', $url, $parameters, [], ['CONTENT_TYPE' => 'application/json'], null, 'json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }


    public function testGetLogsAction()
    {
        $job = new Job();
        $job->setTicket('12345');

        $client = static::createClient();

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('getLogs')
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

        // get the (initialized) serializer before it is destroyed by this mockServices() call, otherwise we do not have custom handlers initialized
        $serializer = static::$kernel->getContainer()->get('abc.job.serializer');

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('cancel')
            ->with($job->getTicket())
            ->willReturn($job);

        $client->request('POST', '/api/jobs/12345/cancel');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        /** @var JobInterface $deserializedObject */
        $deserializedObject = $serializer->deserialize($data, Job::class, 'json');

        $this->assertEquals($job->getStatus(), $deserializedObject->getStatus());
    }

    public function testCancelReturns404IfJobNotFound()
    {
        $job = new Job();
        $job->setTicket('12345');

        $client = static::createClient();

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('cancel')
            ->willThrowException(new TicketNotFoundException($job->getTicket()));

        $client->request('POST', '/api/jobs/12345/cancel');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testRestartAction()
    {
        $job = new Job();
        $job->setTicket('12345');

        $client = static::createClient();

        // get the (initialized) serializer before it is destroyed by this mockServices() call, otherwise we do not have custom handlers initialized
        $serializer = static::$kernel->getContainer()->get('abc.job.serializer');

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('restart')
            ->with($job->getTicket())
            ->willReturn($job);

        $client->request('POST', '/api/jobs/12345/restart');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        /** @var JobInterface $deserializedObject */
        $deserializedObject = $serializer->deserialize($data, Job::class, 'json');

        $this->assertEquals($job->getTicket(), $deserializedObject->getTicket());
    }

    public function testRestartReturns404IfJobNotFound()
    {
        $job = new Job();
        $job->setTicket('12345');

        $client = static::createClient();

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('restart')
            ->willThrowException(new TicketNotFoundException($job->getTicket()));

        $client->request('POST', '/api/jobs/12345/restart');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public static function provideValidPostParameters()
    {
        return [
            [
                [
                    'type'       => 'abc.mailer',
                    'parameters' => [
                        [
                            'to'      => 'to@domain.tld',
                            'from'    => 'from@domain.tld',
                            'message' => 'message body',
                            'subject' => 'subject'
                        ]
                    ]
                ]
            ],
            [
                [
                    'type'       => 'abc.mailer',
                    'parameters' => [
                        [
                            'to'      => 'to@domain.tld',
                            'from'    => 'from@domain.tld',
                            'message' => 'message body',
                            'subject' => 'subject'
                        ]
                    ],
                    'schedules'  => [
                        [
                            'type'       => 'cron',
                            'expression' => '* * * * *',
                        ]
                    ]
                ]
            ],
            [
                [
                    'type' => 'parameterless'
                ]
            ],
            [
                [
                    'type'       => 'parameterless',
                    'parameters' => null
                ]
            ],
            [
                [
                    'type'       => 'parameterless',
                    'parameters' => []
                ]
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

        if (isset($parameters['parameters']) && count($parameters['parameters']) > 0) {
            $message = new Message(
                $parameters['parameters'][0]['to'],
                $parameters['parameters'][0]['from'],
                $parameters['parameters'][0]['subject'],
                $parameters['parameters'][0]['message']
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