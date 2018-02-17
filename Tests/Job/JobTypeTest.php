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
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\JobAwareJob;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeTest extends TestCase
{
    private $serviceId;
    private $type;
    private $callable;
    private $logLevel;

    /**
     * @var JobType
     */
    protected $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->serviceId = 'service-id';
        $this->type      = 'job-type';
        $this->callable  = [new JobAwareJob, JobAwareJob::getMethodName()];
        $this->logLevel  = Logger::ERROR;
        $this->subject   = new JobType($this->serviceId, $this->type, $this->callable, $this->logLevel);
    }

    /**
     * @dataProvider getInvalidConstructorArgs
     * @expectedException \InvalidArgumentException
     *
     * @param mixed    $serviceId
     * @param mixed    $type
     * @param callable $callable
     * @param mixed    $logLevel
     *
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
        $this->assertEquals(JobAwareJob::class, $this->subject->getClass());
    }

    public function testGetMethod()
    {
        $this->assertEquals(JobAwareJob::getMethodName(), $this->subject->getMethod());
    }

    public function testGetCallable()
    {
        $this->assertEquals([new JobAwareJob, JobAwareJob::getMethodName()], $this->subject->getCallable());
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

    public function testGetParameterType()
    {
        $this->subject->setParameterTypes(['Type1', 'Type2']);
        $this->assertEquals('Type1', $this->subject->getParameterType(0));
        $this->assertEquals('Type2', $this->subject->getParameterType(1));
    }

    public function testGetSetParameterTypeOptions()
    {
        $this->assertTrue(is_array($this->subject->getParameterTypeOptions()));
        $this->assertTrue(is_array($this->subject->getParameterTypeOptions(1)));

        $this->subject->setParameterTypeOptions([['Parameter1TypeOptions'], ['Parameter2TypeOptions']]);

        $this->assertEquals([['Parameter1TypeOptions'], ['Parameter2TypeOptions']], $this->subject->getParameterTypeOptions());
        $this->assertEquals(['Parameter1TypeOptions'], $this->subject->getParameterTypeOptions(0));
        $this->assertEquals(['Parameter2TypeOptions'], $this->subject->getParameterTypeOptions(1));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetParameterTypeWithIndexNotDefined()
    {
        $this->subject->getParameterType(0);
    }

    /**
     * @dataProvider provideDataForGetIndices
     * @param array $parameterTypes
     * @param       $expectedIndices
     */
    public function testGetIndicesOfSerializableParameters(array $parameterTypes, $expectedIndices)
    {
        $this->subject->setParameterTypes($parameterTypes);

        $this->assertEquals($expectedIndices, $this->subject->getIndicesOfSerializableParameters());
    }

    public function testGetSetReturnType()
    {
        $this->assertNull($this->subject->getReturnType());

        $this->subject->setReturnType('response');
        $this->assertEquals('response', $this->subject->getReturnType());
    }

    public function testGetSetReturnTypeOptions()
    {
        $this->assertTrue(is_array($this->subject->getReturnTypeOptions()));

        $this->subject->setReturnTypeOptions(['ReturnTypeOptions']);
        $this->assertEquals(['ReturnTypeOptions'], $this->subject->getReturnTypeOptions());
    }

    public function testGetSetQueue()
    {
        $this->assertNull($this->subject->getQueue());

        $this->subject->setQueue('queue');
        $this->assertEquals('queue', $this->subject->getQueue());
    }

    /**
     * @return array
     */
    public static function getInvalidConstructorArgs()
    {
        $callable = function () {
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

    /**
     * @return array
     */
    public static function provideDataForGetIndices()
    {
        return [
            [['@runtimeParameter'], []],
            [['@runtimeParameter', 'Type1'], [1]],
            [['@runtimeParameter', 'Type1', 'Type2'], [1, 2]],
            [['Type1', '@runtimeParameter'], [0]],
            [['Type1', '@runtimeParameter', 'Type2'], [0, 2]],
            [['Type1', '@runtimeParameter1', 'Type2', '@runtimeParameter2'], [0, 2]]
        ];
    }
}