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

use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Serializer\DeserializationContext;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Abc\Bundle\JobBundle\Serializer\SerializationContext;
use Abc\Bundle\JobBundle\Serializer\SerializerInterface;
use Abc\Bundle\JobBundle\Test\MockHelper;
use phpmock\phpunit\PHPMock;

/**
 * @runTestsInSeparateProcesses
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SerializationHelperTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $json_decode;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $json_encode;

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

        $this->json_decode = $this->getFunctionMock(MockHelper::getNamespace(SerializationHelper::class), 'json_decode');
        $this->json_encode = $this->getFunctionMock(MockHelper::getNamespace(SerializationHelper::class), 'json_encode');

        $this->subject = new SerializationHelper($this->registry, $this->serializer);
    }

    public function testSerializeParameters()
    {
        $jobType = $this->getMock(JobTypeInterface::class);

        $jobType->expects($this->any())
            ->method('getIndicesOfSerializableParameters')
            ->willReturn([0, 1, 3]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $jobType->expects($this->exactly(2))
            ->method('getParameterTypeOptions')
            ->withConsecutive([0], [3])
            ->willReturnOnConsecutiveCalls(
                ['groups' => ['groupA', 'groupB'], 'version' => '1'],
                ['groups' => ['groupC'], 'version' => '2']
            );

        $expectedContext1 = new SerializationContext();
        $expectedContext1->setGroups(['groupA', 'groupB']);
        $expectedContext1->setVersion('1');
        $expectedContext2 = new SerializationContext();
        $expectedContext2->setGroups(['groupC']);
        $expectedContext2->setVersion('2');

        $this->serializer->expects($this->exactly(2))
            ->method('serialize')
            ->withConsecutive(
                ['Parameter1', 'json', $expectedContext1],
                ['Parameter3', 'json', $expectedContext2]
            )
            ->willReturnOnConsecutiveCalls(
                'SerializedParameter1',
                'SerializedParameter3'
            );


        $this->json_encode->expects($this->once())
            ->with(['SerializedParameter1', null, 'SerializedParameter3'])
            ->willReturn('SerializedParameters');

        $this->assertEquals('SerializedParameters', $this->subject->serializeParameters('JobType', ['Parameter1', null, 'Parameter3']));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSerializeParametersWithInvalidNumberOfParameters() {
        $jobType = $this->getMock(JobTypeInterface::class);

        $jobType->expects($this->any())
            ->method('getIndicesOfSerializableParameters')
            ->willReturn([0]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->subject->serializeParameters('JobType', ['Parameter1', 'Parameter2']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSerializeParametersWithJsonEncodeFails() {
        $jobType = $this->getMock(JobTypeInterface::class);

        $jobType->expects($this->any())
            ->method('getIndicesOfSerializableParameters')
            ->willReturn([0]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $jobType->expects($this->once())
            ->method('getParameterTypeOptions')
            ->willReturn(array());

        $this->serializer->expects($this->exactly(1))
            ->method('serialize')
            ->with('Parameter1', 'json', new SerializationContext())
            ->willReturn('SerializedParameter1');

        $this->json_encode->expects($this->once())
            ->with(['SerializedParameter1'])
            ->willReturn(false);

        $this->subject->serializeParameters('JobType', ['Parameter1']);
    }

    public function testDeserializeParameters()
    {
        $jobType = $this->getMock(JobTypeInterface::class);

        $jobType->expects($this->any())
            ->method('getIndicesOfSerializableParameters')
            ->willReturn([0, 1, 3]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->json_decode->expects($this->once())
            ->with('SerializedParameters')
            ->willReturn(['SerializedParameter1', null, 'SerializedParameter2']);

        $jobType->expects($this->exactly(2))
            ->method('getParameterType')
            ->withConsecutive([0], [3])
            ->willReturnOnConsecutiveCalls('ParameterType1', 'ParameterType2');

        $jobType->expects($this->exactly(2))
            ->method('getParameterTypeOptions')
            ->withConsecutive([0], [3])
            ->willReturnOnConsecutiveCalls(
                ['groups' => ['groupA', 'groupB'], 'version' => '1'],
                ['groups' => ['groupC'], 'version' => '2']
            );

        $expectedContext1 = new DeserializationContext();
        $expectedContext1->setGroups(['groupA', 'groupB']);
        $expectedContext1->setVersion('1');
        $expectedContext2 = new DeserializationContext();
        $expectedContext2->setGroups(['groupC']);
        $expectedContext2->setVersion('2');

        $this->serializer->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(
                ['SerializedParameter1', 'ParameterType1', 'json', $expectedContext1],
                ['SerializedParameter2', 'ParameterType2', 'json', $expectedContext2]
            )
            ->willReturnOnConsecutiveCalls(
                'DeserializedParameter1',
                'DeserializedParameter2'
            );

        $this->assertEquals(['DeserializedParameter1', null, 'DeserializedParameter2'], $this->subject->deserializeParameters('JobType', 'SerializedParameters'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeserializeParametersWithInvalidNumberOfParameters()
    {
        $jobType = $this->getMock(JobTypeInterface::class);

        $jobType->expects($this->any())
            ->method('getIndicesOfSerializableParameters')
            ->willReturn([0]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->json_decode->expects($this->once())
            ->with('SerializedParameters')
            ->willReturn(['SerializedParameter1', 'SerializedParameter2']);

        $this->subject->deserializeParameters('JobType', 'SerializedParameters');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDeserializeParametersWithJsonDecodeFails()
    {
        $jobType = $this->getMock(JobTypeInterface::class);

        $jobType->expects($this->any())
            ->method('getIndicesOfSerializableParameters')
            ->willReturn([0]);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $this->json_decode->expects($this->once())
            ->with('SerializedParameters')
            ->willReturn(false);

        $this->subject->deserializeParameters('JobType', 'SerializedParameters');
    }

    public function testSerializeReturnValue()
    {
        $jobType = $this->getMock(JobTypeInterface::class);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $jobType->expects($this->once())
            ->method('getReturnTypeOptions')
            ->willReturn(['groups' => ['group1', 'group2'], 'version' => '12345']);

        $expectedContext = new SerializationContext();
        $expectedContext->setGroups(['group1', 'group2']);
        $expectedContext->setVersion('12345');

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with('ReturnValue', 'json', $expectedContext);

        $this->subject->serializeReturnValue('JobType', 'ReturnValue');
    }

    public function testDeserializeReturnValue()
    {
        $jobType = $this->getMock(JobTypeInterface::class);

        $this->registry->expects($this->once())
            ->method('get')
            ->with('JobType')
            ->willReturn($jobType);

        $jobType->expects($this->once())
            ->method('getReturnType')
            ->willReturn('ReturnType');

        $jobType->expects($this->once())
            ->method('getReturnTypeOptions')
            ->willReturn(['groups' => ['group1', 'group2'], 'version' => '12345']);

        $expectedContext = new DeserializationContext();
        $expectedContext->setGroups(['group1', 'group2']);
        $expectedContext->setVersion('12345');

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('ReturnValue', 'ReturnType', 'json', $expectedContext);

        $this->subject->deserializeReturnValue('JobType', 'ReturnValue');
    }
}