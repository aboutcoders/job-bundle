<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Context;

use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\Context\Exception\ParameterNotFoundException;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new Context();
    }

    public function testConstruct()
    {
        $parameters = array('foo' => 'bar');
        $subject    = new Context($parameters);

        $this->assertSame($parameters, $subject->all());
    }

    /**
     * @expectedException
     */
    public function testGetThrowsParameterNotFoundException()
    {
        try {
            $this->subject->get('foo');
            $this->fail('no exception thrown');
        } catch (ParameterNotFoundException $e) {
            $this->assertEquals('A parameter with the name "foo" does not exist', $e->getMessage());
            $this->assertEquals('foo', $e->getName());
        }
    }

    public function testConstructLowerCasesKeys()
    {
        $subject = new Context(['fooBar' => 'FOOBAR']);
        $this->assertTrue($subject->has('foobar'));
        $this->assertTrue($subject->has('fooBar'));
    }

    public function testSetGetHas()
    {
        $parameter = new \stdClass;
        $this->subject->set('foo', $parameter);

        $this->assertTrue($this->subject->has('foo'));
        $this->assertSame($parameter, $this->subject->get('foo'));
    }

    public function testRemove()
    {
        $parameter = new \stdClass;
        $this->subject->set('foo', $parameter);
        $this->subject->remove('foo');

        $this->assertEmpty($this->subject->all());
    }

    public function testClear()
    {
        $this->subject->set('foo', 'bar');
        $this->subject->clear();

        $this->assertEmpty($this->subject->all());
    }
}