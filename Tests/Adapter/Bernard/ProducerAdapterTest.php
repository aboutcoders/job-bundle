<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Adapter\Bernard;

use Abc\Bundle\JobBundle\Adapter\Bernard\ProducerAdapter;
use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ProducerAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Producer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $producer;

    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var SerializationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var ProducerAdapter
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->producer   = $this->createMock(Producer::class);
        $this->registry   = $this->createMock(JobTypeRegistry::class);
        $this->manager    = $this->createMock(ManagerInterface::class);
        $this->serializer = $this->createMock(SerializationHelper::class);
        $this->subject    = new ProducerAdapter($this->producer, $this->registry, $this->serializer);
        $this->subject->setManager($this->manager);
    }

    /**
     * @dataProvider provideMessage
     * @param Message $message
     */
    public function testProduce(Message $message)
    {
        $queue   = 'QueueName';
        $jobType = $this->createMock(JobTypeInterface::class);
        $jobType->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $arguments = ['type' => $message->getType()];
        if (null != $message->getTicket()) {
            $arguments['ticket'] = $message->getTicket();
        }
        if (null != $message->getParameters()) {
            $arguments['parameters'] = 'SerializedParameters';
            $this->serializer->expects($this->once())
                ->method('serializeParameters')
                ->with($message->getType(), $message->getParameters())
                ->willReturn('SerializedParameters');
        }

        $producerMessage = new DefaultMessage('ConsumeJob', $arguments);

        $this->registry->expects($this->once())
            ->method('get')
            ->with($message->getType())
            ->willReturn($jobType);

        $this->producer->expects($this->once())
            ->method('produce')
            ->with($producerMessage, $queue);

        $this->subject->produce($message);
    }

    /**
     * @dataProvider getMessageParameters
     * @param      $type
     * @param null $ticket
     * @param null $parameters
     */
    public function testConsumeJob($type, $ticket = null, $parameters = null)
    {
        $expectedMessage = new Message($type, $ticket);
        $arguments       = ['type' => $type];
        if (null != $ticket) {
            $arguments['ticket'] = $ticket;
        }
        if (null != $parameters) {
            $expectedMessage->setParameters(['DeserializedParameters']);
            $arguments['parameters'] = $parameters;
            $this->serializer->expects($this->once())
                ->method('deserializeParameters')
                ->with($type, $parameters)
                ->willReturn(['DeserializedParameters']);
        }

        $producerMessage = new DefaultMessage('ConsumeJob', $arguments);

        $this->manager->expects($this->once())
            ->method('handleMessage')
            ->with($expectedMessage);

        $this->subject->consumeJob($producerMessage);
    }

    /**
     * @return array
     */
    public static function provideMessage()
    {
        return [
            [new Message('JobType')],
            [new Message('JobType', 'JobTicket')],
            [new Message('JobType', 'JobTicket', array('foobar'))]
        ];
    }

    /**
     * @return array
     */
    public static function getMessageParameters()
    {
        return [
            ['JobType', 'JobTicket'],
            ['JobType', 'JobTicket'],
            ['JobType', 'JobTicket', 'SerializedParameters'],
        ];
    }
}