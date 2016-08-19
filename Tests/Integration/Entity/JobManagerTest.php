<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Entity;

use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Entity\Job;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Entity\JobManager;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobManagerTest extends DatabaseKernelTestCase
{

    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var JobManager
     */
    private $subject;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->registry   = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMock(SerializerInterface::class);

        $this->subject = new JobManager(
            $this->getEntityManager(),
            Job::class,
            $this->getScheduleManager(),
            $this->serializer,
            $this->registry
        );
    }

    /**
     * @param array $criteria
     * @param int   $expectedCount
     * @dataProvider getFindByCountData
     */
    public function testFindByCount($criteria, $expectedCount)
    {
        /** @var Status $status */
        $job = $this->subject->create('foo');
        $job->setStatus(Status::REQUESTED());

        $this->subject->save($job);

        $job = $this->subject->create('bar');
        $job->setStatus(Status::PROCESSING());

        $this->subject->save($job);

        $job = $this->subject->create('foobar');
        $job->setStatus(Status::PROCESSING());

        $this->subject->save($job);

        $count = $this->subject->findByCount($criteria);

        $this->assertEquals($expectedCount, $count);
    }

    public function testFindByTickets()
    {
        $job1 = $this->subject->create('foo');
        $job1->setStatus(Status::REQUESTED());

        $job2 = $this->subject->create('bar');
        $job2->setStatus(Status::REQUESTED());

        $job3 = $this->subject->create('foobar');
        $job3->setStatus(Status::REQUESTED());

        $this->subject->save($job1);
        $this->subject->save($job2);
        $this->subject->save($job3);

        $jobs = $this->subject->findByTypes(array('foo', 'bar'));

        $this->assertCount(2, $jobs);
        $this->assertContains($job1, $jobs);
        $this->assertContains($job2, $jobs);
        $this->assertNotContains($job3, $jobs);
    }

    public function testFindByTypes()
    {
        $job1 = $this->subject->create('foo');
        $job1->setStatus(Status::REQUESTED());

        $job2 = $this->subject->create('bar');
        $job2->setStatus(Status::REQUESTED());

        $job3 = $this->subject->create('foobar');
        $job3->setStatus(Status::REQUESTED());

        $this->subject->save($job1);
        $this->subject->save($job2);
        $this->subject->save($job3);

        $jobs = $this->subject->findByTickets(array($job1->getTicket(), $job2->getTicket()));

        $this->assertCount(2, $jobs);
        $this->assertContains($job1, $jobs);
        $this->assertContains($job2, $jobs);
        $this->assertNotContains($job3, $jobs);
    }

    public function testFindByAgeAndTypes()
    {
        $terminatedAt = new \DateTime;
        $terminatedAt->setTimestamp(strtotime('today - 1 day'));

        $job1 = $this->subject->create('foo');
        $job1->setStatus(Status::REQUESTED());
        $job1->setTerminatedAt($terminatedAt);

        $terminatedAt = new \DateTime;
        $terminatedAt->setTimestamp(strtotime('today - 2 day'));

        $job2 = $this->subject->create('bar');
        $job2->setStatus(Status::REQUESTED());
        $job2->setTerminatedAt($terminatedAt);

        $job3 = $this->subject->create('foo');
        $job3->setStatus(Status::REQUESTED());

        $this->subject->save($job1);
        $this->subject->save($job2);
        $this->subject->save($job3);

        $jobs = $this->subject->findByAgeAndTypes(1);
        $this->assertCount(2, $jobs);
        $this->assertContains($job1, $jobs);
        $this->assertContains($job2, $jobs);

        $jobs = $this->subject->findByAgeAndTypes(2);
        $this->assertCount(1, $jobs);
        $this->assertContains($job2, $jobs);

        $jobs = $this->subject->findByAgeAndTypes(1, array('foo'));
        $this->assertCount(1, $jobs);
        $this->assertContains($job1, $jobs);

        $jobs = $this->subject->findByAgeAndTypes(2, array('foo'));
        $this->assertCount(0, $jobs);
    }

    /**
     * @return array
     */
    public static function getFindByCountData()
    {
        return [
            [['type' => 'foo'], 1],
            [['type' => ['foo', 'bar']], 2],
            [['type' => 'undefined'], 0],
            [['type' => 'foo', 'status' => 'REQUESTED'], 1],
            [['type' => 'foo', 'status' => 'PROCESSING'], 0],
            [['type' => ['$match' => 'foo']], 2],
        ];
    }

    /**
     * @return ScheduleManager
     */
    private function getScheduleManager()
    {
        return $this->getContainer()->get('abc.job.schedule_manager');
    }
}