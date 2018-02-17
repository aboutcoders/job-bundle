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

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Log;
use Abc\Bundle\JobBundle\Model\LogManager;
use Monolog\Formatter\FormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogManagerTest extends TestCase
{
    /**
     * @var LogManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = $this->getMockForAbstractClass(LogManager::class);
    }

    public function testCreate()
    {
        $this->subject->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue(Log::class));

        $schedule = $this->subject->create();

        $this->assertInstanceOf(Log::class, $schedule);
    }

    public function testFindByJobUsesCustomFormatter()
    {
        $ticket = 'Ticket';
        $job    = new Job();
        $job->setTicket($ticket);

        $log_A = $this->buildLog('ChannelA', 'LevelA', 'MessageA');
        $log_B = $this->buildLog('ChannelB', 'LevelB', 'MessageB');

        $job = new Job();
        $job->setTicket('Ticket');

        $this->subject->expects($this->once())
            ->method('findBy')
            ->with(['jobTicket' => $job->getTicket()], ['datetime' => 'ASC'])
            ->willReturn([$log_A, $log_B]);

        $this->assertEquals([
            [
                'channel'    => 'ChannelA',
                'level'      => 'LevelA',
                'level_name' => null,
                'message'    => 'MessageA',
                'datetime'   => null,
                'context'    => [],
                'extra'      => [],
            ],
            [
                'channel'    => 'ChannelB',
                'level'      => 'LevelB',
                'level_name' => null,
                'message'    => 'MessageB',
                'datetime'   => null,
                'context'    => [],
                'extra'      => [],
            ]
        ], $this->subject->findByJob($job));
    }

    public function testDeleteByJob()
    {
        $job = new Job();
        $job->setTicket('Ticket');

        $log_A = $this->buildLog('ChannelA', 'LevelA', 'MessageA');

        $this->subject = $this->getMockForAbstractClass(LogManager::class, [], '', null, null, null, ['delete']);

        $this->subject->expects($this->once())
            ->method('findBy')
            ->with(['jobTicket' => $job->getTicket()])
            ->willReturn([$log_A]);

        $this->subject->expects($this->once())
            ->method('delete')
            ->with($log_A);


        $this->assertEquals(1, $this->subject->deleteByJob($job));
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @return Log
     */
    protected function buildLog($channel, $level, $message)
    {
        $log = new Log;
        $log->setChannel($channel);
        $log->setLevel($level);
        $log->setMessage($message);

        return $log;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}