<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Model;

use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

class JobTest extends \PHPUnit_Framework_TestCase
{
    /** @var SerializerInterface */
    private $serializer;

    public function setUp()
    {
        $foobar = null;

        $this->serializer = SerializerBuilder::create()->configureHandlers(
            function (HandlerRegistry $handlerRegistry) use ($foobar)
            {
                //$handlerRegistry->registerSubscribingHandler($jobHandler);
            }
        )->build();
    }

    public function testCreateSchedule()
    {
        $subject = new Job();
        $schedule = $subject->createSchedule('foo', 'bar');

        $this->assertInstanceOf('Abc\Bundle\JobBundle\Model\Schedule', $schedule);
        $this->assertEquals('foo', $schedule->getType());
        $this->assertEquals('bar', $schedule->getExpression());
    }

    public function testHasSchedule()
    {
        $job = new Job();

        $this->assertFalse($job->hasSchedules());

        $job->addSchedule(new Schedule());

        $this->assertTrue($job->hasSchedules());
    }

    public function testGetExecutionTime()
    {
        $subject      = new Job();
        $createdAt    = new \DateTime('2010-01-01 00:00:00');
        $terminatedAt = new \DateTime('2010-01-01 00:00:01');

        $subject->setCreatedAt($createdAt);
        $subject->setTerminatedAt($terminatedAt);

        $expectedProcessingTime = $subject->getTerminatedAt()->format('U') - $subject->getCreatedAt()->format('U');

        $this->assertEquals($expectedProcessingTime, $subject->getExecutionTime());
    }

    public function testSerializationToJson()
    {
        $message = new Message('to@domain.tld', 'from@domain.tld', 'subject', 'message');

        $job = new Job();
        $job->setType('type');
        $job->setTicket('ticket');
        $job->setParameters([$message, 'foo']);
        $job->setResponse(['response']);

        $data = $this->serializer->serialize($job, 'json');

        $dataArray = json_decode($data, true);

        $this->assertEquals($job->getTicket(), $dataArray['ticket']);
        $this->assertEquals($job->getType(), $dataArray['type']);
        $this->assertEquals($job->getResponse(), $dataArray['response']);
        $this->assertEquals([
            [
                'to' => 'to@domain.tld',
                'from' => 'from@domain.tld',
                'subject' => 'subject',
                'message' => 'message'
            ], 'foo'
        ], $dataArray['parameters']);
    }

    public function testClone()
    {
        $job = new Job;
        $job->setTicket('ticket');

        $clone = clone $job;

        $this->assertTrue($job !== $clone);
        $this->assertNotEquals($job->getTicket(), $clone->getTicket());
    }
}