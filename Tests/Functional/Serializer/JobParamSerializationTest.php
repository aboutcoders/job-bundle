<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Serializer;

use Abc\Bundle\JobBundle\Job\JobParameterArray;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Serializer\Handler\JobParameterArrayHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobParamSerializationTest extends \PHPUnit_Framework_TestCase
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
        $this->setUpSerializer();
    }

    /**
     * @param array  $parameters
     * @param string $type
     * @dataProvider provideParameters
     */
    public function testSerializeParameters(array $parameters, $type)
    {
        $data = $this->serializer->serialize($parameters, 'json');

        $deserializedParameters = $this->serializer->deserialize($data, $type, 'json');

        $this->assertEquals($parameters, $deserializedParameters);
    }

    public function provideParameters()
    {
        return [
            [['foobar'], JobParameterArray::class.'<string>'],
            [[$this->createMessage(), false], JobParameterArray::class.'<'.Message::class.',boolean>'],
            [[$this->createMessage(), 'string'], JobParameterArray::class.'<'.Message::class.',string>'],
            [[null, 'string', null], JobParameterArray::class.'<string,string,string>'],
        ];
    }

    /**
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $message
     * @return Message
     */
    public function createMessage($to = 'to@domain.tld', $from = 'from@domain.tld', $subject = 'Message Subject', $message = 'Message Body')
    {
        return new Message($to, $from, $subject, $message);
    }

    private function setUpSerializer()
    {
        $this->serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistry $handlerRegistry) {
                $handlerRegistry->registerSubscribingHandler(new JobParameterArrayHandler());
            })
            ->build();
    }
}