<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger\Factory;

use Abc\Bundle\JobBundle\Logger\Handler\JobAwareOrmHandler;
use Abc\Bundle\JobBundle\Logger\Handler\OrmHandler;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Logger\Factory\OrmFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class OrmFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var OrmFactory
     */
    private $subject;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(JobTypeRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMock(LogManagerInterface::class);

        $this->subject = new OrmFactory($this->registry, $this->manager);

        new OrmFactory($this->registry, $this->manager);
    }

    public function testCreateHandler()
    {
        $job   = new Job;
        $level = Logger::CRITICAL;
        $debug = true;

        /** @var JobAwareOrmHandler $handler */
        $handler = $this->invokeMethod($this->subject, 'createHandler', [$job, $level, $debug]);

        $this->assertInstanceOf(JobAwareOrmHandler::class, $handler);
        $this->assertEquals($level, $handler->getBubble());
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