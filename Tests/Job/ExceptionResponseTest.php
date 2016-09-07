<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Response;

use Abc\Bundle\JobBundle\Job\ExceptionResponse;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ExceptionResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function testGetCode()
    {
        $subject = new ExceptionResponse('foobar', 100);

        $this->assertSame(100, $subject->getCode());
    }

    public function testGetMessage()
    {
        $subject = new ExceptionResponse('foobar', 100);

        $this->assertSame('foobar', $subject->getMessage());
    }

    public function testSerializationToJson()
    {
        $subject = new ExceptionResponse('foobar', 100);

        $data = $this->serializer->serialize($subject, 'json');

        $object = $this->serializer->deserialize($data, ExceptionResponse::class, 'json');

        $this->assertEquals($subject, $object);
    }
}