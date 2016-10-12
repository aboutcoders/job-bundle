<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Serializer;

use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Serializer\DeserializationContext;
use Abc\Bundle\JobBundle\Serializer\SerializationContext;
use Abc\Bundle\JobBundle\Serializer\Serializer;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JMSSerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var Serializer
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->serializer = $this->createMock(JMSSerializerInterface::class);
        $this->subject    = new Serializer($this->serializer);
    }

    /**
     * @param mixed        $data
     * @param string       $format
     * @param Context|null $context
     * @dataProvider provideSerializationData
     */
    public function testSerialize($data, $format, $context = null)
    {
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($data, $format, $context);

        $this->subject->serialize($data, $format, $context);
    }

    /**
     * @param mixed        $data
     * @param string       $type
     * @param string       $format
     * @param Context|null $context
     * @dataProvider provideDeserializationData
     */
    public function testDeserialize($data, $type, $format, $context = null)
    {
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($data, $type, $format, $context);

        $this->subject->deserialize($data, $type, $format, $context);
    }

    public static function provideSerializationData()
    {
        return [
            ['data', 'format'],
            ['data', 'format', new SerializationContext()]
        ];
    }

    public static function provideDeserializationData()
    {
        return [
            ['data', 'type', 'format'],
            ['data', 'type', 'format', new DeserializationContext()]
        ];
    }
}