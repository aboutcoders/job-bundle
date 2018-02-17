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
use Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata;
use Abc\Bundle\JobBundle\Job\Queue\QueueConfigInterface;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestJob;
use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactoryInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeRegistryTest extends TestCase
{
    /**
     * @var MetadataFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataFactory;

    /**
     * @var QueueConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueConfig;

    /**
     * @var JobTypeRegistry
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $this->queueConfig     = $this->createMock(QueueConfigInterface::class);
        $this->subject         = new JobTypeRegistry($this->metadataFactory, $this->queueConfig);
    }

    public function testAll()
    {
        $callable = array(new TestJob(), 'log');
        $jobType  = new JobType('service-id', 'type', $callable);

        $this->subject->register($jobType);

        $this->assertContains($jobType, $this->subject->all());
    }

    public function testHas()
    {
        $this->assertFalse($this->subject->has(null));

        $this->assertFalse($this->subject->has('foobar'));

        $callable = array(new TestJob(), 'log');
        $jobType  = new JobType('service-id', 'type', $callable);

        $this->subject->register($jobType);

        $this->assertTrue($this->subject->has('type'));
    }

    public function testRegisterLoadsMetadata()
    {
        $callable = array(new TestJob(), 'log');

        $jobType = new JobType('service-id', 'type', $callable, Logger::ERROR);

        $classHierarchyMetadata = $this->getMockBuilder(ClassHierarchyMetadata::class)->disableOriginalConstructor()->getMock();

        $classMetadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();

        $this->metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->with(get_class($callable[0]))
            ->willReturn($classHierarchyMetadata);

        $classHierarchyMetadata->expects($this->once())
            ->method('getRootClassMetadata')
            ->willReturn($classMetadata);

        $classMetadata->expects($this->once())
            ->method('getParameterTypes')
            ->willReturn(['ParameterTypes']);

        $classMetadata->expects($this->once())
            ->method('getParameterOptions')
            ->willReturn(['ParameterTypeOptions']);

        $classMetadata->expects($this->once())
            ->method('getReturnType')
            ->willReturn('ReturnType');

        $classMetadata->expects($this->once())
            ->method('getReturnOptions')
            ->willReturn(['ReturnTypeOptions']);

        $this->subject->register($jobType, true);

        $this->assertSame($jobType, $this->subject->get($jobType->getName()));
        $this->assertEquals(['ParameterTypes'], $jobType->getParameterTypes());
        $this->assertEquals(['ParameterTypeOptions'], $jobType->getParameterTypeOptions());
        $this->assertEquals('ReturnType', $jobType->getReturnType());
        $this->assertEquals(['ReturnTypeOptions'], $jobType->getReturnTypeOptions());
    }

    /**
     * @expectedException \Abc\Bundle\JobBundle\Job\JobTypeNotFoundException
     */
    public function testGetThrowsJobTypeNotFoundException()
    {
        $this->subject->get('foo');
    }

    public function testGetDefaultQueue()
    {
        $this->queueConfig->expects($this->once())
            ->method('getDefaultQueue')
            ->willReturn('DefaultQueue');

        $this->assertEquals('DefaultQueue', $this->subject->getDefaultQueue());
    }

    public function testGetTypeChoices()
    {
        $callable = array(new TestJob(), 'log');
        $jobType  = new JobType('service-id', 'JobType', $callable);

        $this->subject->register($jobType);

        $this->assertEquals(['JobType'], $this->subject->getTypeChoices());
    }
}