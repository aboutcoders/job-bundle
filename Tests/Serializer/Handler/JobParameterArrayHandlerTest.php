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
     * @var JobParameterArrayHandler
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = new JobParameterArrayHandler();
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
     * @param array $data
     * @param array $type
     * @dataProvider provideDataWithoutTypesAdded
     */
    public function testDeserializeJobParameterArrayWithoutTypesAdded(array $data, array $type)
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

    public static function provideDataWithoutTypesAdded()
    {
        return [
            [['foobar'], ['name' => JobParameterArray::class, 'params' => [
                ['name' => 'foobarType', 'params' => []]
            ]]]
        ];
    }

    /**
     * @param array $data
     * @param array $types
     * @dataProvider provideDataWithTypesAdded
     */
    public function testDeserializeJobParameterArrayWithTypesAdded(array $data, array $types)
    {
        $visitor = $this->getMock(VisitorInterface::class);
        $context = $this->getMock(DeserializationContext::class);
        $type    = ['params' => []];
        $dataWithoutTypes = $data;
        $dataWithoutTypes = array_pop($dataWithoutTypes);

        $expectedReturnValue = [];
        $at = 0;
        for ($i = 0; $i < count($types); $i++) {
            if(!is_array($dataWithoutTypes) || !isset($dataWithoutTypes[$i]) || null == $dataWithoutTypes[$i]) {
                $expectedReturnValue[] = null;
            }
            else {
                $context->expects($this->at($at))
                    ->method('accept')
                    ->with($dataWithoutTypes[$i], [
                        'name'   => $types[$i],
                        'params' => []
                    ])
                    ->willReturn('deserializedParam_' . $i);

                $expectedReturnValue[] = 'deserializedParam_' . $i;
                $at++;
            }
        }

        $returnValue = $this->subject->deserializeJobParameterArray($visitor, $data, $type, $context);

        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    /**
     * @param array $data
     * @dataProvider provideDataForInvalidArgumentException
     * @expectedException \JMS\Serializer\Exception\RuntimeException
     */
    public function testDeserializeJobParameterArrayTrowsInvalidArgumentException(array $data)
    {
        $visitor = $this->getMock(VisitorInterface::class);
        $context = $this->getMock(DeserializationContext::class);
        $type    = ['params' => []];

        $this->subject->deserializeJobParameterArray($visitor, $data, $type, $context);
    }

    public static function provideDataWithTypesAdded()
    {
        return [
            [['foobar', ['abc.job.params' => ['foobarType']]], ['foobarType']],
            [[['abc.job.params' => ['foobarType']]], ['foobarType']]

        ];
    }

    public static function provideDataForInvalidArgumentException()
    {
        return [
            [['foobar']],
            [['foobar', ['abc.job.params' => []]]],
        ];
    }
}