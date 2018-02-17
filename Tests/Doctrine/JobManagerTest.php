<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Doctrine;

use Abc\Bundle\JobBundle\Doctrine\JobManager;
use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobManagerTest extends TestCase
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classMetaData;

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var  ScheduleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleManager;

    /**
     * @var SerializationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializationHelper;

    /**
     * @var JobManager
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->class               = Job::class;
        $this->classMetaData       = $this->createMock(ClassMetadata::class);
        $this->objectManager       = $this->createMock(ObjectManager::class);
        $this->repository          = $this->createMock(ObjectRepository::class);
        $this->scheduleManager     = $this->getMockBuilder(ScheduleManager::class)->disableOriginalConstructor()->getMock();
        $this->serializationHelper = $this->getMockBuilder(SerializationHelper::class)->disableOriginalConstructor()->getMock();

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->any())
            ->method('getName')
            ->willReturn($this->class);

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->subject = $this->getMockForAbstractClass(
            JobManager::class,
            [
                $this->objectManager,
                $this->class,
                $this->scheduleManager,
                $this->serializationHelper
            ]
        );
    }

    public function testSetsDeserializationHelper()
    {
        $this->assertAttributeInstanceOf(SerializationHelper::class, 'serializationHelper', \Abc\Bundle\JobBundle\Doctrine\Job::class);
    }

    public function testGetClass()
    {
        $this->assertEquals($this->class, $this->subject->getClass());
    }

    /**
     * @param bool|null $andFlush
     * @dataProvider provideAddFlushValues
     */
    public function testSave($andFlush = null)
    {
        $entity = $this->subject->create('type');

        $this->objectManager->expects($this->once())
            ->method('persist')
            ->with($entity);

        $what = ($andFlush || is_null($andFlush)) ? $this->once() : $this->never();

        $this->objectManager->expects($what)
            ->method('flush');

        is_null($andFlush) ? $this->subject->save($entity) : $this->subject->save($entity, $andFlush);
    }

    public function testRefresh()
    {
        $entity = $this->subject->create('type');

        $this->objectManager->expects($this->once())
            ->method('refresh')
            ->with($entity);

        $this->subject->refresh($entity);
    }

    public function testDelete()
    {
        $entity = $this->subject->create('type');

        $this->objectManager->expects($this->once())
            ->method('remove')
            ->with($entity);

        $this->objectManager->expects($this->once())
            ->method('flush');

        $this->subject->delete($entity);
    }

    public function testFindAll()
    {
        $this->repository->expects($this->once())
            ->method('findAll');

        $this->subject->findAll();
    }

    public function testFindBy()
    {
        $criteria = array('foo');
        $orderBy  = array('foo' => 'bar');
        $limit    = 2;
        $offset   = 1;

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with($criteria, $orderBy, $limit, $offset);

        $this->subject->findBy($criteria, $orderBy, $limit, $offset);
    }

    public static function provideAddFlushValues()
    {
        return [
            [],
            [true],
            [false]
        ];
    }
}