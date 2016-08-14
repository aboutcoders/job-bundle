<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Serializer;

use Abc\Bundle\JobBundle\Job\ExceptionResponse;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class GenericArrayHandlerTest extends KernelTestCase
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();

        $this->serializer = static::$kernel->getContainer()->get('jms_serializer');
    }

    /**
     * @param string $format
     * @dataProvider getSupportedFormats
     */
    public function testArray($format)
    {
        $arg0 = new ExceptionResponse('foobar', 100);
        $arg1 = 'string';
        $arg2 = false;

        $subject = array($arg0, $arg1, $arg2);

        $data = $this->serializer->serialize($subject, $format);

        $array = $this->serializer->deserialize($data, 'GenericArray<Abc\Bundle\JobBundle\Job\ExceptionResponse,string,boolean>', $format);

        $this->assertEquals($subject, $array);
    }


    /**
     * @return array
     */
    public static function getSupportedFormats()
    {
        return array(
            array('json')
        );
    }
}