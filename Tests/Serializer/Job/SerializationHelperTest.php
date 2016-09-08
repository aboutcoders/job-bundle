<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Serializer\Job;

use Abc\Bundle\JobBundle\Job\JobParameterArray;
use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Abc\Bundle\JobBundle\Serializer\SerializerInterface;

class SerializationHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var SerializationHelper
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->registry   = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMock(SerializerInterface::class);
        $this->subject    = new SerializationHelper($this->registry, $this->serializer);
    }

    public function testSerialize()
    {
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with('foobar');

        $this->subject->serialize('foobar');
    }

    public function testDeserializeParameters()
    {
        $jobType = $this->getMock(JobTypeInterface::class);
        $jobType->expects($this->any())
            ->method('getSerializableParameterTypes')
            ->willReturn(['ParamType']);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('foobar', JobParameterArray::class . '<'. 'ParamType' .'>', 'json')
            ->willReturn('ReturnValue');

        $this->assertEquals('ReturnValue', $this->subject->deserializeParameters('foobar', 'JobType'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeserializeParametersThrowsInvalidArgumentException()
    {
        $jobType = $this->getMock(JobTypeInterface::class);
        $jobType->expects($this->any())
            ->method('getSerializableParameterTypes')
            ->willReturn([]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->subject->deserializeParameters('foobar', 'JobType');
    }

    public function testDeserializeResponse()
    {
        $jobType = $this->getMock(JobTypeInterface::class);
        $jobType->expects($this->any())
            ->method('getResponseType')
            ->willReturn('ResponseType');

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('foobar', 'ResponseType', 'json')
            ->willReturn('ReturnValue');

        $this->assertEquals('ReturnValue', $this->subject->deserializeResponse('foobar', 'JobType'));
    }
}