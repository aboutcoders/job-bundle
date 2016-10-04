<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Serializer\Handler;

use Abc\Bundle\JobBundle\Job\JobParameterArray;
use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Serializer\DeserializationContext;
use Abc\Bundle\JobBundle\Serializer\Handler\JobParameterArrayHandler;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\VisitorInterface;
use Metadata\MetadataFactoryInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobParameterArrayHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var JobParameterArrayHandler
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->registry = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->subject  = new JobParameterArrayHandler($this->registry);
    }

    public function testGetSubscribingMethodsListensOnJsonSerialization()
    {
        $this->assertContains([
            'type'      => JobParameterArray::class,
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format'    => 'json',
            'method'    => 'serializeJobParameterArray'
        ], $this->subject->getSubscribingMethods());
    }

    public function testGetSubscribingMethodsListensOnJsonDeserialization()
    {
        $this->assertContains([
            'type'      => JobParameterArray::class,
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'format'    => 'json',
            'method'    => 'deserializeJobParameterArray'
        ], $this->subject->getSubscribingMethods());
    }

    public function testSerializeJobParameterArray()
    {
        $visitor = $this->getMock(VisitorInterface::class);
        $context = $this->getMockForAbstractClass(Context::class);
        $data    = ['data'];
        $type    = ['type'];

        $visitor->expects($this->once())
            ->method('visitArray')
            ->with($data, $type, $context)
            ->willReturn('foobar');

        $this->assertEquals('foobar', $this->subject->serializeJobParameterArray($visitor, $data, $type, $context));
    }

    /**
     * @dataProvider provideDataWithoutTypeAdded
     * @param array $data
     * @param array $type
     */
    public function testDeserializeJobParameterArrayWithoutTypeAdded(array $data, array $type)
    {
        $visitor   = $this->getMock(VisitorInterface::class);
        $context   = $this->getMock(DeserializationContext::class);
        $navigator = new GraphNavigator(
            $this->getMock(MetadataFactoryInterface::class),
            $this->getMock(HandlerRegistryInterface::class),
            $this->getMock(ObjectConstructorInterface::class));

        $expectedReturnValue = [];
        for ($i = 0; $i < count($data); $i++) {
            $context->expects($this->at($i))
                ->method('accept')
                ->with($data[$i], $type['params'][$i])
                ->willReturn('deserializedParam_' . $i);
            $expectedReturnValue[] = 'deserializedParam_' . $i;
        }

        $context->expects($this->once())
            ->method('getNavigator')
            ->willReturn($navigator);

        $visitor->expects($this->once())
            ->method('setNavigator')
            ->with($navigator);

        $returnValue = $this->subject->deserializeJobParameterArray($visitor, $data, $type, $context);

        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    public static function provideDataWithoutTypeAdded()
    {
        return [
            [['foobar'], ['name' => JobParameterArray::class, 'params' => [
                ['name' => 'foobarType', 'params' => []]
            ]]]
        ];
    }

    /**
     * @dataProvider provideDataWithTypeAdded
     * @param JobTypeInterface $jobType
     * @param array  $data
     * @param array  $parameters
     */
    public function testDeserializeJobParameterArrayWithTypeAdded(JobTypeInterface $jobType, array $data, array $parameters)
    {
        $visitor          = $this->getMock(VisitorInterface::class);
        $context          = $this->getMock(DeserializationContext::class);
        $dataWithoutTypes = $data;
        $dataWithoutTypes = array_pop($dataWithoutTypes);

        $this->setUpRegistry($jobType);

        $expectedReturnValue = [];
        $at                  = 0;
        for ($i = 0; $i < count($parameters); $i++) {
            if (!is_array($dataWithoutTypes) || !isset($dataWithoutTypes[$i]) || null == $dataWithoutTypes[$i]) {
                $expectedReturnValue[] = null;
            } else {
                $context->expects($this->at($at))
                    ->method('accept')
                    ->with($dataWithoutTypes[$i], [
                        'name'   => $parameters[$i],
                        'params' => []
                    ])
                    ->willReturn('deserializedParam_' . $i);

                $expectedReturnValue[] = 'deserializedParam_' . $i;
                $at++;
            }
        }

        $returnValue = $this->subject->deserializeJobParameterArray($visitor, $data, ['params' => []], $context);

        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    /**
     * @dataProvider provideDataForInvalidArgumentException
     * @expectedException \JMS\Serializer\Exception\RuntimeException
     *
     * @param array $data
     */
    public function testDeserializeJobParameterArrayTrowsInvalidArgumentException(array $data)
    {
        $visitor = $this->getMock(VisitorInterface::class);
        $context = $this->getMock(DeserializationContext::class);
        $type    = ['params' => []];

        $this->subject->deserializeJobParameterArray($visitor, $data, $type, $context);
    }

    /**
     * @return array
     */
    public function provideDataWithTypeAdded()
    {
        // $jobType, $data, $indices, $parameterTypes
        return [
            [$this->createJobType('abc.job.foobar', ['foobarType']), [['abc.job.type' => 'abc.job.foobar']], ['foobarType']],
            [$this->createJobType('abc.job.foobar', ['@runtimeParameter', 'foobarType']), ['foobar', ['abc.job.type' => 'abc.job.foobar']], ['foobarType']]
        ];
    }

    /**
     * @return array
     */
    public static function provideDataForInvalidArgumentException()
    {
        return [
            [['foobar']],
            [['foobar', ['abc.job.params' => []]]],
        ];
    }

    /**
     * @param JobTypeInterface $jobType
     */
    private function setUpRegistry(JobTypeInterface $jobType)
    {
        $this->registry->expects($this->any())
            ->method('get')
            ->with($jobType->getName())
            ->willReturn($jobType);
    }

    /**
     * @param       $type
     * @param array $parameterTypes
     * @return JobTypeInterface
     */
    private function createJobType($type, array $parameterTypes)
    {
        /**
         * @var JobType|\PHPUnit_Framework_MockObject_MockObject $jobType
         */
        $jobType = $this->getMockBuilder(JobType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();

        $jobType->expects($this->any())
            ->method('getName')
            ->willReturn($type);

        $jobType->setParameterTypes($parameterTypes);

        return $jobType;
    }
}