<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Entity;

use Abc\Bundle\JobBundle\Entity\Log;
use Abc\Bundle\JobBundle\Entity\LogManager;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\LogInterface;
use Doctrine\ORM\EntityManager;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $entityManager
     */
    private $entityManager;

    /**
     * @var LogManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;


    public function setUp()
    {
        $class         = 'Abc\Bundle\JobBundle\Entity\Log';
        $classMetaData = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $classMetaData->expects($this->any())
            ->method('getName')
            ->willReturn($class);

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $entityManager */
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();

        $entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetaData);

        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        $this->entityManager = $entityManager;

        $this->subject = $this->getMockBuilder('Abc\Bundle\JobBundle\Entity\LogManager')
            ->setMethods(['findBy', 'deleteLogs', 'formatLogs'])
            ->setConstructorArgs([$entityManager, $class])
            ->getMock();
    }

    public function testSaveWithExtra()
    {
        $log = $this->subject->create();
        $log->setExtra(['job_ticket']);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function(LogInterface $value) use($log) {
                $extra = $value->getExtra();
                return $log === $value && !isset($extra['job_ticket']);
            }));

        $this->subject->save($log);
    }

    public function testFindByJob()
    {
        $job = new Job();
        $job->setTicket('Ticket');

        $this->subject->expects($this->once())
            ->method('findBy')
            ->with(['jobTicket' => $job->getTicket()])
            ->willReturn(['LogEntity']);

        $this->subject->expects($this->once())
            ->method('formatLogs')
            ->with(['LogEntity'])
            ->willReturn('FormattedEntities');

        $this->assertEquals('FormattedEntities', $this->subject->findByJob($job));
    }

    public function testDeleteByJob()
    {
        $job = new Job();
        $job->setTicket('Ticket');

        $this->subject->expects($this->at(0))
            ->method('findBy')
            ->with(['jobTicket' => $job->getTicket()])
            ->willReturn(['foobar']);

        $this->subject->expects($this->at(1))
            ->method('deleteLogs')
            ->with(['foobar'])
            ->willReturn(5);

        $this->assertEquals(5, $this->subject->deleteByJob($job));
    }
}
