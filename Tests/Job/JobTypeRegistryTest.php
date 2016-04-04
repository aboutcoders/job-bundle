<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job;

use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestCallable;
use Metadata\MetadataFactoryInterface;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataFactory;

    /**
     * @var JobTypeRegistry
     */
    private $subject;

    public function setUp()
    {
        $this->metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');

        $this->subject = new JobTypeRegistry($this->metadataFactory);
    }

    public function testAll()
    {
        $callable = array(new TestCallable(), 'log');
        $jobType = new JobType('service-id', 'type', $callable);

        $this->subject->register($jobType);

        $this->assertContains($jobType, $this->subject->all());
    }

    public function testHas()
    {
        $this->assertFalse($this->subject->has(null));

        $this->assertFalse($this->subject->has('foobar'));

        $callable = array(new TestCallable(), 'log');
        $jobType = new JobType('service-id', 'type', $callable);

        $this->subject->register($jobType);

        $this->assertTrue($this->subject->has('type'));
    }

    public function testRegisterLoadsMetadata()
    {
        $callable = array(new TestCallable(), 'log');

        $jobType = new JobType('service-id', 'type', $callable, Logger::ERROR);

        $classHierarchyMetadata = $this->getMockBuilder('Metadata\ClassHierarchyMetadata')->disableOriginalConstructor()->getMock();

        $classMetadata = $this->getMockBuilder('Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata')->disableOriginalConstructor()->getMock();

        $this->metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->with(get_class($callable[0]))
            ->willReturn($classHierarchyMetadata);

        $classHierarchyMetadata->expects($this->once())
            ->method('getRootClassMetadata')
            ->willReturn($classMetadata);

        $classMetadata->expects($this->once())
            ->method('getMethodArgumentTypes')
            ->willReturn(array('foo', 'bar'));

        $this->subject->register($jobType, true);

        $this->assertSame($jobType, $this->subject->get($jobType->getName()));
    }

    /**
     * @expectedException \Abc\Bundle\JobBundle\Job\JobTypeNotFoundException
     */
    public function testGetThrowsJobTypeNotFoundException()
    {
        $this->subject->get('foo');
    }
}