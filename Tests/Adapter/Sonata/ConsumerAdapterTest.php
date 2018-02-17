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
use Abc\Bundle\JobBundle\Adapter\Sonata\ConsumerAdapter;
use Abc\Bundle\JobBundle\Tests\Adapter\Sonata\Fixtures\TestIterator;
use Abc\ProcessControl\ControllerInterface;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Event\IterateEvent;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerAdapterTest extends TestCase
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
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationDispatcher;

    /**
     * @var ControllerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;

    /**
     * @var BackendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backend;

    /**
     * @var ConsumerAdapter
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->backendProvider        = $this->getMockBuilder(BackendProvider::class)->disableOriginalConstructor()->getMock();
        $this->eventDispatcher        = $this->createMock(EventDispatcherInterface::class);
        $this->notificationDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->controller             = $this->createMock(ControllerInterface::class);
        $this->backend                = $this->createMock(BackendInterface::class);

        $this->subject = new ConsumerAdapter($this->backendProvider, $this->eventDispatcher, $this->notificationDispatcher, $this->controller);
    }

    public function testConsume()
    {
        $queue = 'foobar';
        $message = $this->createMock(MessageInterface::class);
        $iterator = new TestIterator([$message]);

        $this->backendProvider->expects($this->any())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($this->backend);

        $this->backend->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->backend->expects($this->once())
            ->method('handle')
            ->with($message, $this->notificationDispatcher);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(IterateEvent::EVENT_NAME, new IterateEvent($this->backend->getIterator(), $this->backend, $message));

        // the $iterator does not pop elements so we have to make sure ->tick() does not iterate over the same array multiple times
        $this->subject->consume($queue, ['stop-when-empty' => true]);
    }

    public function testConsumeChecksController()
    {
        $queue = 'foobar';
        $message = $this->createMock(MessageInterface::class);
        $iterator = new TestIterator([$message, $message]);

        $this->backendProvider->expects($this->once())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($this->backend);

        $this->backend->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->controller->expects($this->any())
            ->method('doStop')
            ->willReturn(true);

        $this->backend->expects($this->never())
            ->method('handle');

        $this->subject->consume($queue);
    }

    public function testConsumeChecksMaxRuntime()
    {
        $queue = 'foobar';
        $message = $this->createMock(MessageInterface::class);
        $iterator = new TestIterator([$message, $message]);

        $this->backendProvider->expects($this->once())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($this->backend);

        $this->backend->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->backend->expects($this->never())
            ->method('handle');

        $this->subject->consume($queue, [
            'max-runtime' => -1
        ]);
    }

    public function testConsumeChecksMaxMessages()
    {
        $queue = 'foobar';
        $message = $this->createMock(MessageInterface::class);
        $iterator = new TestIterator([$message, $message]);

        $this->backendProvider->expects($this->once())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($this->backend);

        $this->backend->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->backend->expects($this->once())
            ->method('handle');

        $this->subject->consume($queue, [
            'max-messages' => 1
        ]);
    }

    public function testConsumeIteratesOverAllMessages()
    {
        $queue = 'foobar';
        $message = $this->createMock(MessageInterface::class);
        $iterator = new TestIterator([$message, $message]);

        $this->backendProvider->expects($this->any())
            ->method('getBackend')
            ->with($queue)
            ->willReturn($this->backend);

        $this->backend->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->backend->expects($this->exactly(2))
            ->method('handle');

        // the $iterator does not pop elements so we have to make sure ->tick() does not iterate over the same array multiple times
        $this->subject->consume($queue, ['stop-when-empty' => true]);
    }
}