<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Listener;

use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Listener\JobListener;
use Abc\Bundle\JobBundle\Logger\LoggerFactoryInterface;
use Abc\Bundle\JobBundle\Model\Job;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobListenerTest extends TestCase
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var LoggerFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var JobListener
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->manager = $this->createMock(ManagerInterface::class);
        $this->factory = $this->createMock(LoggerFactoryInterface::class);
        $this->subject = new JobListener($this->manager, $this->factory);
    }

    public function testOnPreExecuteRegistersLogger()
    {
        $job     = new Job('JobTicket');
        $context = new Context();
        $logger  = new NullLogger();
        $event   = new ExecutionEvent($job, $context);

        $this->factory->expects($this->once())
            ->method('create')
            ->with($job)
            ->willReturn($logger);

        $this->subject->onPreExecute($event);

        $this->assertTrue($context->has('abc.logger'));
        $this->assertSame($logger, $context->get('abc.logger'));
    }

    public function testOnPreExecuteRegistersManager()
    {
        $job     = new Job('JobTicket');
        $context = new Context();
        $event   = new ExecutionEvent($job, $context);

        $this->subject->onPreExecute($event);

        $this->assertTrue($context->has('abc.manager'));
        $this->assertSame($this->manager, $context->get('abc.manager'));
    }
}