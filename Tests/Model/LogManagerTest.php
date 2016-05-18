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

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $manager = $this->buildManager();

        $manager->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue('Abc\Bundle\JobBundle\Model\Log'));

        $schedule = $manager->create();

        $this->assertInstanceOf('Abc\Bundle\JobBundle\Model\Log', $schedule);
    }

    public function testFormatLogsUsesLineFormatterByDefault()
    {
        $ticket = 'Ticket';
        $job = new Job();
        $job->setTicket($ticket);

        $log_A = $this->buildLog('ChannelA', 'LevelA', 'MessageA');
        $log_B = $this->buildLog('ChannelB', 'LevelB', 'MessageB');

        $manager = $this->buildManager();

        $returnValue = $this->invokeMethod($manager, 'formatLogs', [[$log_A, $log_B]]);

        $lines = explode("\n", $returnValue);

        $this->assertContains('ChannelA', $lines[0]);
        $this->assertContains('ChannelB', $lines[1]);
    }

    public function testFindByJobUsesCustomFormatter()
    {
        $ticket = 'Ticket';
        $job = new Job();
        $job->setTicket($ticket);

        $log_A = $this->buildLog('ChannelA', 'LevelA', 'MessageA');
        $log_B = $this->buildLog('ChannelB', 'LevelB', 'MessageB');

        $formatter = $this->buildFormatter();

        $formatter->expects($this->at(0))
            ->method('format')
            ->with($this->contains('ChannelA'))
            ->willReturn('FormattedA');

        $formatter->expects($this->at(1))
            ->method('format')
            ->with($this->contains('ChannelB'))
            ->willReturn('FormattedB');

        $manager = $this->buildManager();

        $manager->setFormatter($formatter);

        $returnValue = $this->invokeMethod($manager, 'formatLogs', [[$log_A, $log_B]]);

        $this->assertEquals('FormattedAFormattedB', $returnValue);
    }

    /**
     * @return LogManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function buildManager()
    {
        return $this->getMockForAbstractClass('Abc\Bundle\JobBundle\Model\LogManager');
    }

    /**
     * @return FormatterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function buildFormatter()
    {
        return $this->getMock('Monolog\Formatter\FormatterInterface');
    }

    /**
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