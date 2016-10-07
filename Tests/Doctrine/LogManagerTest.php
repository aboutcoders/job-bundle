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

use Abc\Bundle\JobBundle\Model\Log;
use Abc\Bundle\JobBundle\Doctrine\LogManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogManagerTest extends \PHPUnit_Framework_TestCase
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
     * @var LogManager
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->class           = Log::class;
        $this->classMetaData   = $this->createMock(ClassMetadata::class);
        $this->objectManager   = $this->createMock(ObjectManager::class);
        $this->repository      = $this->createMock(ObjectRepository::class);

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
            LogManager::class,
            array(
                $this->objectManager,
                $this->class
            )
        );
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
        $entity = $this->subject->create();

        $this->objectManager->expects($this->once())
            ->method('persist')
            ->with($entity);

        $what = ($andFlush || is_null($andFlush)) ? $this->once() : $this->never();

        $this->objectManager->expects($what)
            ->method('flush');

        is_null($andFlush) ? $this->subject->save($entity) : $this->subject->save($entity, $andFlush);
    }

    public function testDelete()
    {
        $entity = $this->subject->create();

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

    public function testFindByChannel()
    {
        $channel = 'Channel';

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['channel' => $channel]);

        $this->subject->findByChannel($channel);
    }

    /**
     * @return array
     */
    public static function provideAddFlushValues()
    {
        return [
            [],
            [true],
            [false]
        ];
    }
}