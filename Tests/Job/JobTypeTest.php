<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job;

use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestJobAwareCallable;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeTest extends \PHPUnit_Framework_TestCase
{
    private $serviceId;
    private $type;
    private $callable;
    private $logLevel;

    /** @var JobType */
    protected $subject;

    public function setUp()
    {
        $this->serviceId = 'service-id';
        $this->type      = 'job-type';
        $this->callable  = array(new TestJobAwareCallable, TestJobAwareCallable::getMethodName());
        $this->logLevel  = Logger::ERROR;

        $this->subject = new JobType($this->serviceId, $this->type, $this->callable, $this->logLevel);
    }

    /**
     * @param mixed    $serviceId
     * @param mixed    $type
     * @param callable $callable
     * @param mixed    $logLevel
     * @dataProvider getInvalidConstructorArgs
     * @expectedException \InvalidArgumentException
     */
    public function testConstructThrowsInvalidArgumentException($serviceId, $type, $callable, $logLevel = null)
    {
        new JobType($serviceId, $type, $callable, $logLevel);
    }

    public function testGetServiceId()
    {
        $this->assertEquals($this->serviceId, $this->subject->getServiceId());
    }

    public function testGetType()
    {
        $this->assertEquals($this->type, $this->subject->getName());
    }

    public function testGetClass()
    {
        $this->assertEquals('Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestJobAwareCallable', $this->subject->getClass());
    }

    public function testGetMethod()
    {
        $this->assertEquals(TestJobAwareCallable::getMethodName(), $this->subject->getMethod());
    }

    public function testGetSetLogLevel()
    {
        $this->assertEquals($this->logLevel, $this->subject->getLogLevel());

        $this->subject->setLogLevel(Logger::ALERT);
        $this->assertEquals(Logger::ALERT, $this->subject->getLogLevel());
    }

    public function testGetSetParameterTypes()
    {
        $this->assertTrue(is_array($this->subject->getParameterTypes()));

        $this->subject->setParameterTypes(array('string'));
        $this->assertEquals(array('string'), $this->subject->getParameterTypes());
    }

    public function testGetSetResponseType()
    {
        $this->assertNull($this->subject->getResponseType());

        $this->subject->setResponseType('response');
        $this->assertEquals('response', $this->subject->getResponseType());
    }

    public function testGetSetFormClass()
    {
        $this->assertNull($this->subject->getFormClass());

        $this->subject->setFormClass('form-service-id');
        $this->assertEquals('form-service-id', $this->subject->getFormClass());
    }

    public static function getInvalidConstructorArgs()
    {
        $callable = function ()
        {
        };

        return [
            ['service-id', new \stdClass, $callable],
            ['service-id', false, $callable],
            ['service-id', true, $callable],
            ['service-id', 100, $callable],
            ['service-id', 'type', 'callable'],
            ['service-id', 'type', $callable, new \stdClass],
            ['service-id', 'type', $callable, 'false'],
            ['service-id', 'type', $callable, false],
            ['service-id', 'type', $callable, 1000]
        ];
    }
}