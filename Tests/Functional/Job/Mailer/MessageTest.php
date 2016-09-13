<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Job\Mailer;

use Abc\Bundle\JobBundle\Job\Mailer\Message;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MessageTest extends KernelTestCase
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

    /**
     * @param Message $message
     * @param $numOfExpectedErrors
     * @dataProvider provideValidationData
     */
    public function testValidation($message, $numOfExpectedErrors)
    {
        static::bootKernel();

        /**
         * @var ValidatorInterface $validator
         */
        $validator = static::$kernel->getContainer()->get('validator');

        $errors = $validator->validate($message);

        $this->assertCount($numOfExpectedErrors, $errors);
    }

    public function testSerializationToJson()
    {
        $subject = new Message('to', 'from', 'subject', 'message');

        $data = $this->serializer->serialize($subject, 'json');

        $object = $this->serializer->deserialize($data, get_class($subject), 'json');

        $this->assertEquals($subject, $object);
    }

    /**
     * @return array
     */
    public static function provideValidationData()
    {
        return [
            [new Message('mail@domain.tld', 'mail@domain.tld', '', ''), 0],
            [new Message('asd', 'mail@domain.tld', '', ''), 1],
            [new Message('mail@domain.tld', '', '', ''), 1],
            [new Message('', '', '', ''), 2]
        ];
    }
}