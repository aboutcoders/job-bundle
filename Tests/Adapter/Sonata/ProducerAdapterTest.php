<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Adapter\Sonata;

use Abc\Bundle\JobBundle\Adapter\Sonata\BackendProvider;
use Abc\Bundle\JobBundle\Adapter\Sonata\ProducerAdapter;
use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ProducerAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BackendProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendProvider;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

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
        $this->backendProvider = $this->createMock(BackendProvider::class);
        $this->manager         = $this->createMock(ManagerInterface::class);
        $this->registry        = $this->createMock(JobTypeRegistry::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->serializer      = $this->createMock(SerializationHelper::class);

        $this->registry->expects($this->any())
            ->method('getDefaultQueue')
            ->willReturn('default');

        $this->subject = new ProducerAdapter($this->backendProvider, $this->eventDispatcher, $this->registry, $this->serializer);
        $this->subject->setManager($this->manager);
    }

    public function testSetManager()
    {
        $this->subject->setManager($this->manager);

        $this->assertAttributeSame($this->manager, 'manager', $this->subject);
    }

    /**
     * @dataProvider provideMessage
     * @param Message $message
     */
    public function testProduce(Message $message)
    {
        $queue   = 'foobar';
        $backend = $this->createMock(BackendInterface::class);

        $jobType = $this->createMock(JobTypeInterface::class);
        $jobType->expects($this->any())
            ->method('getQueue')
            ->willReturn($queue);

        $this->registry->expects($this->any())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->backendProvider->expects($this->once())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($backend);

        $expectedBody['type'] = $message->getType();
        if (null != $message->getTicket()) {
            $expectedBody['ticket'] = $message->getTicket();
        }
        if (null != $message->getParameters()) {
            $expectedBody['parameters'] = 'SerializedParameters';

            $this->serializer->expects($this->once())
                ->method('serializeParameters')
                ->with($message->getType(), $message->getParameters())
                ->willReturn('SerializedParameters');
        }

        $backend->expects($this->once())
            ->method('createAndPublish')
            ->with('JobType', $expectedBody);

        $this->subject->produce($message);
    }

    /**
     * @expectedException \Exception
     */
    public function testProduceThrowsExceptionsThrownByBackend()
    {
        $queue   = 'foobar';
        $message = new Message('type', 'ticket');
        $backend = $this->createMock(BackendInterface::class);

        $jobType = $this->createMock(JobTypeInterface::class);
        $jobType->expects($this->any())
            ->method('getQueue')
            ->willReturn($queue);

        $this->registry->expects($this->any())
            ->method('get')
            ->with('type')
            ->willReturn($jobType);

        $this->backendProvider->expects($this->once())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($backend);

        $backend->expects($this->once())
            ->method('createAndPublish')
            ->willThrowException(new \Exception);

        $this->subject->produce($message);
    }

    /**
     * @dataProvider getMessageParameters
     * @param string      $type
     * @param string      $ticket
     * @param string|null $parameters
     */
    public function testProcess($type, $ticket, $parameters = null)
    {
        $body = [
            'type'   => $type,
            'ticket' => $ticket
        ];

        $expectedMessage = new Message($type, $ticket);
        if (null != $parameters) {
            $body['parameters'] = $parameters;
            $expectedMessage->setParameters(['DeserializedParameters']);

            $this->serializer->expects($this->once())
                ->method('deserializeParameters')
                ->with('typeB', $parameters)
                ->willReturn(['DeserializedParameters']);
        }

        $event = $this->createConsumerEvent($type, $body);

        $this->manager->expects($this->once())
            ->method('handleMessage')
            ->with($this->equalTo($expectedMessage));

        $this->subject->process($event);
    }

    public static function getMessageParameters()
    {
        return [
            ['typeA', 'ticket_1'],
            ['typeB', 'ticket_2'],
            ['typeB', 'ticket_2', 'SerializedParameters'],
        ];
    }

    public static function provideMessage()
    {
        return [
            [new Message('JobType')],
            [new Message('JobType', 'JobTicket')],
            [new Message('JobType', 'JobTicket', array('foobar'))]
        ];
    }

    private function createConsumerEvent($type, $messageBody)
    {
        $message = $this->createMock(MessageInterface::class);

        $message->expects($this->any())
            ->method('getBody')
            ->willReturn($messageBody);

        $message->expects($this->any())
            ->method('getValue')
            ->willReturnCallback(
                function ($key, $default) use ($messageBody) {
                    if (is_array($messageBody) && isset($messageBody[$key])) {
                        return $messageBody[$key];
                    }

                    return $default;
                }
            );

        $message->expects($this->any())
            ->method('getType')
            ->willReturn($type);

        return new ConsumerEvent($message);
    }
}