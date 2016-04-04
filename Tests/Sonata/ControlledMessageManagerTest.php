<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Sonata;

use Abc\Bundle\JobBundle\Sonata\ControlledMessageManager;
use Abc\ProcessControl\Controller;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ControlledMessageManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Controller|\PHPUnit_Framework_MockObject_MockObject */
    protected $controller;
    /** @var MessageManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;
    /** @var ControlledMessageManager */
    protected $subject;

    public function setUp()
    {
        $this->controller = $this->getMock('Abc\ProcessControl\Controller');
        $this->manager    = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $this->subject    = new ControlledMessageManager($this->controller, $this->manager);
    }

    /**
     * @expectedException \Abc\Bundle\JobBundle\Sonata\IterationStoppedException
     */
    public function testFindByTypesThrowsException()
    {
        $types     = array();
        $state     = 'state';
        $batchSize = 'batchSize';

        $this->controller->expects($this->once())
            ->method('doExit')
            ->willReturn(true);

        $this->subject->findByTypes($types, $state, $batchSize);
    }

    public function testFindByTypesDelegates()
    {
        $types     = array();
        $state     = 'state';
        $batchSize = 'batchSize';

        $this->controller->expects($this->once())
            ->method('doExit')
            ->willReturn(false);

        $this->manager->expects($this->once())
            ->method('findByTypes')
            ->with($types, $state, $batchSize)
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->findByTypes($types, $state, $batchSize));
    }

    public function testGetClass()
    {
        $this->manager->expects($this->once())
        ->method('getClass')
        ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->getClass());
    }

    public function testFindAll()
    {
        $this->manager->expects($this->once())
            ->method('findAll')
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->findAll());
    }

    public function testFindBy()
    {
        $criteria = array();
        $orderBy = array();
        $limit = 5;
        $offset = 10;

        $this->manager->expects($this->once())
            ->method('findBy')
            ->with($criteria, $orderBy, $limit, $offset)
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->findBy($criteria, $orderBy, $limit, $offset));
    }

    public function testFindOneBy()
    {
        $criteria = array();
        $orderBy = array();

        $this->manager->expects($this->once())
            ->method('findOneBy')
            ->with($criteria, $orderBy)
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->findOneBy($criteria, $orderBy));
    }

    public function testFind()
    {
        $id = 5;

        $this->manager->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->find($id));
    }

    public function testCreate()
    {
        $this->manager->expects($this->once())
            ->method('create')
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->create());
    }

    public function testSave()
    {
        $entity = 'foo';
        $andFlush = true;

        $this->manager->expects($this->once())
            ->method('save')
            ->with($entity, $andFlush)
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->save($entity, $andFlush));
    }

    public function testDelete()
    {
        $entity = 'foo';
        $andFlush = true;

        $this->manager->expects($this->once())
            ->method('delete')
            ->with($entity, $andFlush)
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->delete($entity, $andFlush));
    }

    public function testGetTableName()
    {
        $this->manager->expects($this->once())
            ->method('getTableName')
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->getTableName());
    }

    public function testConnection()
    {
        $this->manager->expects($this->once())
            ->method('getConnection')
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->getConnection());
    }

    public function testCountStates()
    {
        $this->manager->expects($this->once())
            ->method('countStates')
            ->willReturn('foo');

        $this->assertEquals('foo', $this->subject->countStates());
    }

    public function testCleanup()
    {
        $maxAge = 5;

        $this->manager->expects($this->once())
            ->method('cleanup')
            ->with($maxAge);

        $this->subject->cleanup($maxAge);
    }

    public function testCancel()
    {
        $message = $this->getMock('Sonata\NotificationBundle\Model\MessageInterface');

        $this->manager->expects($this->once())
            ->method('cancel')
            ->with($message);

        $this->subject->cancel($message);
    }

    public function testRestart()
    {
        $message = $this->getMock('Sonata\NotificationBundle\Model\MessageInterface');

        $this->manager->expects($this->once())
            ->method('restart')
            ->with($message);

        $this->subject->restart($message);
    }

    public function testFindByAttempts()
    {
        $types = array();
        $state = 'foo';
        $batchSize = 5;
        $maxAttempts = 10;
        $attemptDelay = 20;

        $this->manager->expects($this->once())
            ->method('findByAttempts')
            ->with($types, $state, $batchSize, $maxAttempts, $attemptDelay);

        $this->subject->findByAttempts($types, $state, $batchSize, $maxAttempts, $attemptDelay);
    }
}