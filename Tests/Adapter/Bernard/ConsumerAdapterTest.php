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

use Abc\Bundle\JobBundle\Adapter\Bernard\ConsumerAdapter;
use Bernard\Consumer;
use Bernard\Queue;
use Bernard\QueueFactory;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConsumerAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Consumer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumer;

    /**
     * @var QueueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueFactory;

    /**
     * @var ConsumerAdapter
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->consumer = $this->getMockBuilder(Consumer::class)->disableOriginalConstructor()->getMock();
        $this->queueFactory = $this->getMockBuilder(QueueFactory::class)->disableOriginalConstructor()->getMock();
        $this->subject = new ConsumerAdapter($this->consumer, $this->queueFactory);
    }

    public function testConsume() {

        $queue = $this->getMock(Queue::class);
        $options = array('foo' => 'bar');

        $this->queueFactory->expects($this->once())
            ->method('create')
            ->with('foobar')
            ->willReturn($queue);

        $this->consumer->expects($this->once())
            ->method('consume')
            ->with($queue, $options);

        $this->subject->consume('foobar', $options);
    }
}