<?php
/*
* This file is part of the job-bundle package.
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
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectRepository;
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

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $class         = Log::class;
        $classMetaData = $this->createMock(ClassMetadata::class);

        $classMetaData->expects($this->any())
            ->method('getName')
            ->willReturn($class);

        $repository = $this->createMock(ObjectRepository::class);

        /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $entityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetaData);

        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        $this->entityManager = $entityManager;

        $this->subject = $this->getMockBuilder(LogManager::class)
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
}
