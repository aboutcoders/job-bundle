<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger;

use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Logger\Handler\BaseHandlerFactory;
use Abc\Bundle\JobBundle\Logger\Handler\HandlerFactoryRegistry;
use Abc\Bundle\JobBundle\Logger\LoggerFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LoggerFactoryTest extends TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var HandlerFactoryRegistry
     */
    private $handlerFactory;

    /**
     * @var int
     */
    private $level;

    /**
     * @var boolean
     */
    private $bubble;

    /**
     * @var LoggerFactory
     */
    private $subject;

    public function setUp()
    {
        $this->registry       = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->handlerFactory = new HandlerFactoryRegistry();
        $this->level          = -100;
        $this->bubble         = false;
        $this->subject        = new LoggerFactory($this->registry, $this->handlerFactory, $this->level, $this->bubble);
    }

    /**
     * @param int $level
     * @dataProvider provideLevels
     */
    public function testCreateHandlers($level)
    {
        $job = new Job();
        $job->setType('JobType');

        $handler = $this->createMock(HandlerInterface::class);
        $factory = $this->getMockBuilder(BaseHandlerFactory::class)->disableOriginalConstructor()->getMock();
        $jobType = $this->createMock(JobTypeInterface::class);

        $this->handlerFactory->register($factory);

        $jobType->expects($this->any())
            ->method('getLogLevel')
            ->willReturn($level);

        $this->registry->expects($this->once())
            ->method('get')
            ->with($job->getType())
            ->willReturn($jobType);

        if (false === $level) {
            $this->assertInstanceOf(NullLogger::class, $this->subject->create($job));
        } else {
            $expectedLevel = $level == null ? $this->level : $level;
            $factory->expects($this->once())
                ->method('createHandler')
                ->with($job, $expectedLevel, $this->bubble)
                ->willReturn($handler);

            $logger = $this->subject->create($job);
            $this->assertInstanceOf(Logger::class, $logger);

            $this->assertContains($handler, $logger->getHandlers());
        }
    }

    public function testCreatesLoggerWithAddedHandlers()
    {
        $job = new Job();
        $job->setType('JobType');

        $handler      = $this->createMock(HandlerInterface::class);
        $extraHandler = $this->createMock(HandlerInterface::class);
        $factory      = $this->getMockBuilder(BaseHandlerFactory::class)->disableOriginalConstructor()->getMock();
        $jobType      = $this->createMock(JobTypeInterface::class);;

        $this->handlerFactory->register($factory);

        $jobType->expects($this->any())
            ->method('getLogLevel')
            ->willReturn(Logger::CRITICAL);

        $this->registry->expects($this->once())
            ->method('get')
            ->with($job->getType())
            ->willReturn($jobType);

        $factory->expects($this->once())
            ->method('createHandler')
            ->willReturn($handler);

        $this->subject->addHandler($extraHandler);

        $logger = $this->subject->create($job);

        $this->assertContains($handler, $logger->getHandlers());
        $this->assertContains($extraHandler, $logger->getHandlers());
    }

    /**
     * @return array
     */
    public static function provideLevels()
    {
        return [
            [Logger::INFO,],
            [Logger::INFO],
            [null],
            [false],
        ];
    }
}