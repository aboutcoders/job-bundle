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
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ProducerAdapter
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->backendProvider = $this->getMockBuilder(BackendProvider::class)->disableOriginalConstructor()->getMock();
        $this->manager         = $this->createMock(ManagerInterface::class);
        $this->registry        = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger          = $this->createMock(LoggerInterface::class);

        $this->registry->expects($this->any())
            ->method('getDefaultQueue')
            ->willReturn('default');

        $this->subject = new ProducerAdapter($this->backendProvider, $this->eventDispatcher, $this->registry, $this->logger);
        $this->subject->setManager($this->manager);
    }

    public function testSetManager()
    {
        $this->subject->setManager($this->manager);

        $this->assertAttributeSame($this->manager, 'manager', $this->subject);
    }

    public function testProduce()
    {
        $queue   = 'foobar';
        $message = new Message('type', 'ticket', 'callback');
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
            ->with('type', ['ticket' => 'ticket']);

        $this->subject->produce($message);
    }

    /**
     * @expectedException \Exception
     */
    public function testProduceThrowsExceptionsThrownByBackend()
    {
        $queue   = 'foobar';
        $message = new Message('type', 'ticket', 'callback');
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
     * @param string $type
     * @param string $ticket
     * @dataProvider getEventData
     */
    public function testProcess($type, $ticket)
    {
        $body = array(
            'ticket' => $ticket
        );

        $event = $this->createConsumerEvent($type, $body);

        $expectedMessage = new Message($type, $ticket);

        $this->manager->expects($this->once())
            ->method('onMessage')
            ->with($expectedMessage);

        $this->subject->process($event);
    }

    /**
     * @param ConsumerEvent $event
     * @dataProvider getInvalidEvent
     * @expectedException \InvalidArgumentException
     */
    public function testProcessThrowsInvalidArgumentException(ConsumerEvent $event)
    {
        $this->manager->expects($this->never())
            ->method('onMessage');

        $this->subject->process($event);
    }

    public static function getEventData()
    {
        return array(
            array('typeA', 'ticket_1'),
            array('typeB', 'ticket_2')
        );
    }

    public function getInvalidEvent()
    {
        return array(
            array($this->createConsumerEvent('foobar', array())),
            array($this->createConsumerEvent('foobar', array('foobar')))
        );
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