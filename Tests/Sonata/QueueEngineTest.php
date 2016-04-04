<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Sonata;

use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\Message;
use Abc\Bundle\JobBundle\Sonata\QueueEngine;
use Psr\Log\LoggerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class QueueEngineTest extends \PHPUnit_Framework_TestCase
{
    /** @var BackendInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $backend;
    /** @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $manager;
    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $eventDispatcher;
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var QueueEngine */
    private $subject;

    public function setUp()
    {
        $this->backend         = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $this->manager         = $this->getMock('Abc\Bundle\JobBundle\Job\ManagerInterface');
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->logger          = $this->getMock('Psr\Log\LoggerInterface');

        $this->subject = new QueueEngine($this->backend, $this->eventDispatcher, $this->logger);
        $this->subject->setManager($this->manager);
    }

    public function testSetManager()
    {
        $this->subject->setManager($this->manager);

        $this->assertAttributeSame($this->manager, 'manager', $this->subject);
    }

    public function testPublish()
    {
        $message = new Message('type', 'ticket', 'callback');

        $this->backend->expects($this->once())
            ->method('createAndPublish')
            ->with(QueueEngine::MESSAGE_PREFIX . 'type', array('ticket' => 'ticket'));

        $this->subject->publish($message);
    }

    /**
     * @expectedException \Exception
     */
    public function testPublishThrowsExceptionsThrownByBackend()
    {
        $message = new Message('type', 'ticket', 'callback');

        $this->backend->expects($this->once())
            ->method('createAndPublish')
            ->willThrowException(new \Exception);

        $this->subject->publish($message);
    }

    /**
     * @param string      $type
     * @param string      $ticket
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
        $message = $this->getMock('Sonata\NotificationBundle\Model\MessageInterface');

        $message->expects($this->any())
            ->method('getBody')
            ->willReturn($messageBody);

        $message->expects($this->any())
            ->method('getValue')
            ->willReturnCallback(
                function ($key, $default) use ($messageBody)
                {
                    if(is_array($messageBody) && isset($messageBody[$key]))
                    {
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