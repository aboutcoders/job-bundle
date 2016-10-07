<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Model;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobManager;
use Abc\Bundle\JobBundle\Model\ScheduleInterface;
use Abc\Bundle\JobBundle\Model\Schedule;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Abc\Bundle\JobBundle\Model\JobManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = $this->getMockForAbstractClass(JobManager::class);
    }

    /**
     * @param string                 $type
     * @param null                   $parameters
     * @param ScheduleInterface|null $schedule
     * @dataProvider provideArguments
     */
    public function testCreate($type, $parameters = null, $schedule = null)
    {
        $this->subject->expects($this->any())
            ->method('getClass')
            ->willReturn(Job::class);

        $entity = $this->subject->create($type, $parameters, $schedule);

        $this->assertInstanceOf(Job::class, $entity);
        $this->assertEquals($type, $entity->getType());

        if(!is_null($schedule))
        {
            $this->assertContains($schedule, $entity->getSchedules());
        }
    }

    public function testFindByTicket()
    {
        $ticket = 'foobar';

        $this->subject->expects($this->any())
            ->method('findBy')
            ->with(array('ticket' => $ticket));

        $this->subject->findByTicket($ticket);
    }

    public function testIsManagerRecognizesManagedJobs() {

        $this->subject->expects($this->any())
            ->method('getClass')
            ->willReturn(Job::class);
        
        $this->assertTrue($this->subject->isManagerOf(new Job()));
    }

    public function testIsManagerRejectsForeignJobs() {

        $this->subject->expects($this->any())
            ->method('getClass')
            ->willReturn(Job::class);

        /**
         * @var JobInterface $foreignJob
         */
        $foreignJob = $this->createMock(JobInterface::class);

        $this->assertFalse($this->subject->isManagerOf($foreignJob));
    }

    /**
     * @return array
     */
    public static function provideArguments()
    {
        return array(
            array('type'),
            array('type', 'parameters'),
            array('type', 'parameters', new Schedule()),
        );
    }
}