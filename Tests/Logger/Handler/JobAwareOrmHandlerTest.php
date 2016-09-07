<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger\Handler;

use Abc\Bundle\JobBundle\Entity\Log;
use Abc\Bundle\JobBundle\Logger\Handler\JobAwareOrmHandler;
use Abc\Bundle\JobBundle\Logger\Handler\OrmHandler;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobAwareOrmHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var JobAwareOrmHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->manager = $this->getMock(LogManagerInterface::class);;
        $this->subject = $this->getMockBuilder(JobAwareOrmHandler::class)
            ->setConstructorArgs([$this->manager])
            ->setMethods(['populateLog'])
            ->getMock();
    }

    public function testInvokesParentConstructor()
    {
        $level  = Logger::CRITICAL;
        $bubble = false;

        $subject = new JobAwareOrmHandler($this->manager, $level, $bubble);

        $this->assertInstanceOf(OrmHandler::class, $subject);
        $this->assertAttributeSame($level, 'level', $subject);
        $this->assertAttributeSame($bubble, 'bubble', $subject);
    }

    public function testWrite()
    {
        $log = new Log();

        $job = new Job();
        $job->setTicket('JobTicket');

        $record                        = [];
        $record['extra']['job_ticket'] = $job->getTicket();

        $this->manager->expects($this->once())
            ->method('create')
            ->willReturn($log);

        $this->subject->expects($this->once())
            ->method('populateLog')
            ->with($log, $record);

        $this->manager->expects($this->once())
            ->method('save')
            ->with($log);

        $this->subject->setJob($job);
        $this->invokeMethod($this->subject, 'write', [[]]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     * @return mixed
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}