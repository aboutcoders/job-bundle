<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Metadata;

use Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ClassMetadataTest extends TestCase
{
    /**
     * @var ClassMetadata
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = new ClassMetadata('\stdClass');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetParameterTypeWithMethodNotDefined()
    {
        $this->subject->setParameterType('MethodName', 'ParamName', 'Type');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetParameterTypeWithTypeNotDefined()
    {
        $this->subject->addMethod('MethodName', []);
        $this->subject->setParameterType('MethodName', 'ParamName', 'Type');
    }

    public function testGetParameterTypes()
    {
        $this->assertTrue(is_array($this->subject->getParameterTypes('MethodName')) && empty($this->subject->getParameterTypes('MethodName')));

        $this->subject->addMethod('MethodName', ['param1', 'param2']);
        $this->subject->setParameterType('MethodName', 'param1', 'Type1');
        $this->subject->setParameterType('MethodName', 'param2', 'Type2');

        $this->assertEquals(['Type1', 'Type2'], $this->subject->getParameterTypes('MethodName'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetParameterOptionsWithMethodNotDefined()
    {
        $this->subject->setParameterOptions('MethodName', 'ParamName', []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetParameterOptionsWithTypeNotDefined()
    {
        $this->subject->addMethod('MethodName', []);
        $this->subject->setParameterOptions('MethodName', 'ParamName', []);
    }

    public function testGetParameterOptions()
    {

        $this->assertTrue(is_array($this->subject->getParameterOptions('MethodName')) && empty($this->subject->getParameterOptions('MethodName')));

        $this->subject->addMethod('MethodName', ['param1', 'param2']);
        $this->subject->setParameterOptions('MethodName', 'param2', ['foo' => 'bar']);

        $this->assertEquals([[], ['foo' => 'bar']], $this->subject->getParameterOptions('MethodName'));
    }

    public function testReturnType()
    {
        $this->assertNull($this->subject->getReturnType('MethodName'));

        $this->subject->setReturnType('MethodName', 'Type');

        $this->assertEquals('Type', $this->subject->getReturnType('MethodName'));
    }

    public function testReturnOptions()
    {
        $this->assertTrue(is_array($this->subject->getReturnOptions('MethodName')));

        $this->subject->setReturnOptions('MethodName', ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->subject->getReturnOptions('MethodName'));
    }
}