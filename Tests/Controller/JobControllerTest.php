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

use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Tests\DatabaseWebTestCase;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobControllerTest extends DatabaseWebTestCase
{
    /** @var SerializerInterface */
    private $serializer;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @param array $parameters
     * @param int   $expectedNumOfItems
     * @param int   $expectedTotalCount
     * @dataProvider cgetDataProvider
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

        if(!is_null($parameters))
        {
            $url .= '?' . http_build_query($parameters);
        }


        $client->request(
            'GET',
            $url,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            null,
            'json'
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        /** @var JobList $list */
        $list = $this->serializer->deserialize($data, 'Abc\Bundle\JobBundle\Model\JobList', 'json');

        $this->assertCount($expectedNumOfItems, $list->getItems());
        $this->assertEquals($expectedTotalCount, $list->getTotalCount());
    }

    /**
     * @param $parameters
     * @dataProvider providePostData
     */
    public function testPostAction($parameters, $expectedStatusCode)
    {
        $client = static::createClient();

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


        // echo $client->getResponse()->getContent();


        /*
        echo $client->getResponse()->getContent();


        $this->assertJsonResponse($client->getResponse(), 200);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $collectedMessages = $mailCollector->getMessages();
        $this->assertGreaterThan(0, $collectedMessages);
        $message = $collectedMessages[0];

        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('foobar subject', $message->getSubject());*/

    }

    public static function providePostData()
    {
        return [
            [
                [
                    'type' => 'mailer',
                    'parameters' => [
                        'to' => 'to@domain.tld',
                        'from' => 'from@domain.tld',
                        'message' => 'message body',
                        'subject' => 'subject',
                    ]
                ],
                200
            ],
            [
                [
                    'type' => 'mailer',
                    'parameters' => [
                        'to' => 'to@domain.tld',
                        'from' => 'from@domain.tld',
                        'message' => 'message body',
                        'subject' => 'subject',
                    ],
                    'schedules' => [
                        [
                            'type' => 'cron',
                            'expression' => '* * * * *',
                        ]
                    ]
                ],
                200
            ],
            [
                [
                    'type' => 'mailer',
                    'parameters' => [
                        'to' => 'foobar',
                        'from' => 'from@domain.tld',
                        'message' => 'message body',
                        'subject' => 'subject',
                    ]
                ],
                400
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
    public function cgetDataProvider()
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
                ['page' => 3, 'sortCol' => 'type', 'sortDir' => 'DESC', 'limit' => 1],
                1,
                3
            ]
        ];
    }
}