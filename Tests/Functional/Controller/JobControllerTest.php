<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Controller;

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Model\Schedule;
use Abc\Bundle\JobBundle\Serializer\SerializerInterface;
use Abc\Bundle\JobBundle\Test\DatabaseWebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

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
        $this->serializer    = $this->getMock(SerializerInterface::class);
        $this->validator     = $this->getMock(ValidatorInterface::class);
        $this->entityManager = $this->getMock(JobManagerInterface::class);
    }

    /**
     * @dataProvider provideValidListData
     * @param array $parameters
     */
    public function testListActionWithValidParameters($parameters)
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

    /**
     * @dataProvider provideInvalidListData
     * @param array $parameters
     */
    public function testListActionWithInvalidParameters($parameters)
    {
        $url = '/api/jobs?' . http_build_query($parameters);;

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

        $data = json_decode($client->getResponse()->getContent(), true);
    }

    public function testGetAction()
    {
        $client = static::createClient();

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('get')
            ->willThrowException(new TicketNotFoundException('12345'));

        $client->request('GET', '/api/jobs/12345');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testAddAction()
    {
        $parameters = ['type' => 'parameterless'];
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

        $client = static::createClient(['environment' => 'validate_rest']);

        $this->mockServices([
            'abc.job.manager' => $this->manager
        ]);

        $client->request('POST', $url, $parameters, [], ['CONTENT_TYPE' => 'application/json'], null, 'json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdateAction()
    {
        $parameters = ['type' => 'parameterless'];
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

        $this->mockServices(['abc.job.manager' => $this->manager]);

        $this->manager->expects($this->once())
            ->method('restart')
            ->willThrowException(new TicketNotFoundException($job->getTicket()));

        $client->request('POST', '/api/jobs/12345/restart');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @return array An array of GET parameters
     */
    public static function provideValidListData()
    {
        return [
            [
                null
            ],
            [
                ['page' => 1, 'sortCol' => 'type', 'sortDir' => 'ASC', 'limit' => 1]
            ],
            [
                ['sortCol' => 'ticket']
            ],
            [
                ['sortCol' => 'type']
            ],
            [
                ['sortCol' => 'status']
            ],
            [
                ['sortCol' => 'createdAt']
            ],
            [
                ['sortCol' => 'terminatedAt']
            ],
            [
                ['sortDir' => 'ASC']
            ],
            [
                ['sortDir' => 'DESC']
            ],
            [
                ['criteria' => null]
            ],
            [
                ['criteria' => []]
            ],
            [
                ['criteria' => ['ticket' => 'f2c89c2d-7502-11e6-9861-0800271ec67e', 'status' => 'PROCESSING', 'type' => 'abc.mailer']]
            ]
        ];
    }

    /**
     * @return array An array of GET parameters
     */
    public static function provideInvalidListData()
    {
        return [
            [['page' => 'a']],
            [['limit' => 'a']],
            [['sortDir' => 'a']],
            [['sortCol' => 'a']],
            [['criteria' => ['status' => 'foobar']]],
            [['criteria' => ['ticket' => 'foobar']]],
            [['criteria' => ['type' => 'foobar']]],
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
                $schedule = new Schedule($scheduleParameters['type'], $scheduleParameters['expression']);
                $job->addSchedule($schedule);
            }
        }

        return $job;
    }
}