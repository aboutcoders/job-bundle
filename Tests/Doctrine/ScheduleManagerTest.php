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

use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Entity\Schedule;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleManagerTest extends \PHPUnit_Framework_TestCase
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

    /** @var ScheduleManager */
    private $subject;


    public function setUp()
    {
        $this->class         = Schedule::class;
        $this->classMetaData = $this->getMock(ClassMetadata::class);
        $this->objectManager = $this->getMock(ObjectManager::class);
        $this->repository    = $this->getMock(ObjectRepository::class);

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->any())
            ->method('getName')
            ->willReturn($this->class);

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->subject = new ScheduleManager($this->objectManager, $this->class);
    }

    /**
     * @param string|null $type
     * @param string|null $expression
     * @param bool|null $active
     * @dataProvider getCreateArguments
     */
    public function testCreate($type = null, $expression = null, $active = null)
    {
        $schedule = $this->subject->create($type, $expression, $active);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertEquals($type, $schedule->getType());
        $this->assertEquals($expression, $schedule->getExpression());
        $this->assertEquals($active == null ? true : $active, $schedule->getIsActive());
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

    public function testFindSchedules()
    {
        $limit = 5;
        $offset = 10;

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(array('isActive' => true), array(), $limit, $offset)
            ->willReturn('foobar');

        $result = $this->subject->findSchedules($limit, $offset);

        $this->assertSame('foobar', $result);
    }

    public static function getCreateArguments()
    {
        return array(
            array(),
            array('type'),
            array('type', 'expression'),
            array('type', 'expression', true),
            array('type', 'expression', false),
        );
    }
}