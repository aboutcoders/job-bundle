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

use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Logger\Handler\BaseHandlerFactory;
use Abc\Bundle\JobBundle\Logger\Handler\HandlerFactory;
use Abc\Bundle\JobBundle\Logger\LoggerFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LoggerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var HandlerFactory
     */
    private $handlerFactory;

    /**
     * @var LoggerFactory
     */
    private $subject;

    public function setUp()
    {
        $this->registry       = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->handlerFactory = new HandlerFactory();
        $this->subject        = new LoggerFactory($this->registry, $this->handlerFactory);
    }

    /**
     * @param $level
     * @dataProvider provideLevels
     */
    public function testCreateHandlers($level)
    {
        $job = new Job();
        $job->setType('JobType');

        $handler = $this->getMock(HandlerInterface::class);
        $factory = $this->getMockBuilder(BaseHandlerFactory::class)->disableOriginalConstructor()->getMock();
        $jobType = $this->getMockBuilder(JobType::class)->disableOriginalConstructor()->getMock();

        $this->handlerFactory->addFactory($factory);

        $jobType->expects($this->any())
            ->method('getLogLevel')
            ->willReturn($level);

        $this->registry->expects($this->once())
            ->method('get')
            ->with($job->getType())
            ->willReturn($jobType);

        if(false == $level){
            $this->assertInstanceOf(NullLogger::class, $this->subject->create($job));
        }else {
            $factory->expects($this->once())
                ->method('createHandler')
                ->with($job, $level)
                ->willReturn($handler);

            $logger = $this->subject->create($job);
            $this->assertInstanceOf(Logger::class, $logger);
        }
    }

    public static function provideLevels() {
        return [
            ['info'],
            [false]
        ];
    }
}