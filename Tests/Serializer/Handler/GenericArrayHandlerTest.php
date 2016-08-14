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

use Abc\Bundle\JobBundle\Serializer\Handler\GenericArrayHandler;
use JMS\Serializer\Context;
use JMS\Serializer\VisitorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class GenericArrayHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var VisitorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $visitor;

    /**
     * @var GenericArrayHandler
     */
    private $subject;

    public function setUp()
    {
        $this->context = $this->getMockForAbstractClass(Context::class);
        $this->visitor = $this->getMock(VisitorInterface::class);
        $this->subject = new GenericArrayHandler();
    }

    public function testDeserializeWithNull()
    {
        $this->assertNull($this->subject->deserializeArray($this->visitor, null, array(), $this->context));
    }

    /**
     * @expectedException \JMS\Serializer\Exception\RuntimeException
     */
    public function testDeserializeWithMoreArgumentsThanTypes()
    {
        $this->assertNull($this->subject->deserializeArray($this->visitor, array('foo' => 'bar'), array('params' => array()), $this->context));
    }
}