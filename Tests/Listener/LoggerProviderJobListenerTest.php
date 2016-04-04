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
use Abc\Bundle\JobBundle\Job\Logger\FactoryInterface;
use Abc\Bundle\JobBundle\Listener\LoggerProviderJobListener;
use Abc\Bundle\JobBundle\Model\Job;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LoggerProviderJobListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var FactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $factory;
    /** @var LoggerProviderJobListener */
    private $subject;

    public function setUp()
    {
        $this->factory = $this->getMock('Abc\Bundle\JobBundle\Job\Logger\FactoryInterface');

        $this->subject = new LoggerProviderJobListener($this->factory);
    }


    public function testOnPreExecute()
    {
        $job     = new Job('JobTicket');
        $context = new Context();
        $logger  = new NullLogger();
        $event = new ExecutionEvent($job, $context);

        $this->factory->expects($this->once())
            ->method('create')
            ->with($job)
            ->willReturn($logger);

        $this->subject->onPreExecute($event);

        $this->assertTrue($context->has('logger'));
        $this->assertEquals($logger, $context->get('logger'));
    }
}