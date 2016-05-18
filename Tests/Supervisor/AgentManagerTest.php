<?php

/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Abc\Bundle\JobBundle\Tests\Supervisor;

use Abc\Bundle\JobBundle\Model\AgentInterface;
use Abc\Bundle\JobBundle\Supervisor\AgentManager;
use Supervisor\Process;
use YZ\SupervisorBundle\Manager\SupervisorManager;
use Supervisor\Supervisor;

class AgentManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var SupervisorManager|\PHPUnit_Framework_MockObject_MockObject */
    private $supervisorManager;

    /** @var Supervisor|\PHPUnit_Framework_MockObject_MockObject */
    private $supervisor;

    /** @var Process|\PHPUnit_Framework_MockObject_MockObject */
    private $process;

    private static $processInfo = [
        'description' => 'pid 7837, uptime 0:00:41',
        'pid' => 7837,
        'stderr_logfile' => '/vagrant/app/logs/supervisor_queue-agent_default_error.log',
        'stop' => 1455989380,
        'logfile' => '/vagrant/app/logs/supervisor_queue-agent_default.log',
        'exitstatus' => 0,
        'spawnerr' => '',
        'now' => 1455989422,
        'group' => 'setmeup',
        'name' => 'queue-agent_default',
        'statename' => 'RUNNING',
        'start' => 1455989381,
        'state' => 20,
        'stdout_logfile' => '/vagrant/app/logs/supervisor_queue-agent_default.log'
    ];

    /** @var AgentManager */
    private $subject;


    public function setUp()
    {
        $this->process = $this->getMockBuilder('Supervisor\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $this->process->expects($this->any())
            ->method('getName')
            ->willReturn(static::$processInfo['name']);

        $this->process->expects($this->any())
            ->method('getGroup')
            ->willReturn(static::$processInfo['group']);

        $this->process->expects($this->any())
            ->method('getProcessInfo')
            ->willReturn(static::$processInfo);

        $this->supervisor = $this->getMockBuilder('Supervisor\Supervisor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->supervisor->expects($this->any())
            ->method('getName')
            ->willReturn('SupervisorName');

        $this->supervisor->expects($this->any())
            ->method('getProcesses')
            ->willReturn(array($this->process));

        $this->supervisorManager = $this->getMockBuilder('YZ\SupervisorBundle\Manager\SupervisorManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->supervisorManager->expects($this->any())
            ->method('getSupervisors')
            ->willReturn([$this->supervisor]);

        $this->subject = new AgentManager($this->supervisorManager);
    }

    public function testFindAll()
    {
        $agents = $this->subject->findAll();

        $this->assertCount(1, $agents);

        /** @var AgentInterface $agent */
        $agent = array_pop($agents);

        $this->assertEquals('queue-agent_default', $agent->getName());
        $this->assertEquals('RUNNING', $agent->getStatus());
    }

    public function testFindById()
    {
        $this->assertInstanceOf('Abc\Bundle\JobBundle\Model\AgentInterface', $this->subject->findById('setmeup:queue-agent_default'));
    }

    public function testRefresh()
    {
        $processInfo = static::$processInfo;

        $updatedProcessInfo = $processInfo;
        $updatedProcessInfo['state'] = 40;

        $returnValues = array($updatedProcessInfo, $processInfo);

        $this->process = $this->getMockBuilder('Supervisor\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $this->process->expects($this->any())
            ->method('getName')
            ->willReturn(static::$processInfo['name']);

        $this->process->expects($this->any())
            ->method('getGroup')
            ->willReturn(static::$processInfo['group']);

        $this->process->expects($this->any())
            ->method('getProcessInfo')
            ->willReturnCallback(function() use (&$returnValues){
                return array_pop($returnValues);
            });

        $this->supervisor = $this->getMockBuilder('Supervisor\Supervisor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->supervisor->expects($this->any())
            ->method('getName')
            ->willReturn('SupervisorName');

        $this->supervisor->expects($this->any())
            ->method('getProcesses')
            ->willReturn(array($this->process));

        $this->supervisorManager = $this->getMockBuilder('YZ\SupervisorBundle\Manager\SupervisorManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->supervisorManager->expects($this->any())
            ->method('getSupervisors')
            ->willReturn([$this->supervisor]);

        $this->subject = new AgentManager($this->supervisorManager);


        $agents = $this->subject->findAll();

        /** @var AgentInterface $agent */
        $agent = array_pop($agents);

        $this->assertEquals('RUNNING', $agent->getStatus());

        $this->subject->refresh($agent);

        $this->assertEquals('STOPPING', $agent->getStatus());
    }


    public function testClear()
    {
        // by expecting method to be invoked twice we ensure hat processes and agents are reloaded after clear()
        $this->supervisor->expects($this->exactly(2))
            ->method('getProcesses')
            ->willReturn(array($this->process));

        $this->subject->findAll();

        $this->subject->clear();

        $this->subject->findAll();
    }

    /**
     * @param bool $wait
     * @dataProvider getTrueFalse
     */
    public function testStartAgent($wait = null)
    {
        $agent = $this->getAgent();

        if(is_null($wait))
        {
            $this->process->expects($this->exactly(1))
                ->method('startProcess')
                ->with(true);

            $this->subject->start($agent);
        }
        else
        {
            $this->process->expects($this->exactly(1))
                ->method('startProcess')
                ->with($wait);

            $this->subject->start($agent, $wait);
        }
    }

    /**
     * @param bool $wait
     * @dataProvider getTrueFalse
     */
    public function testStopAgent($wait = null)
    {
        $agent = $this->getAgent();

        if(is_null($wait))
        {
            $this->process->expects($this->exactly(1))
                ->method('stopProcess')
                ->with(true);

            $this->subject->stop($agent);
        }
        else
        {
            $this->process->expects($this->exactly(1))
                ->method('stopProcess')
                ->with($wait);

            $this->subject->stop($agent, $wait);
        }
    }

    /**
     * @param bool $wait
     * @dataProvider getTrueFalse
     */
    public function testStartAll($wait = null)
    {
        if(is_null($wait))
        {
            $this->process->expects($this->exactly(1))
                ->method('startProcess')
                ->with(true);

            $this->subject->startAll();
        }
        else
        {
            $this->process->expects($this->exactly(1))
                ->method('startProcess')
                ->with($wait);

            $this->subject->startAll($wait);
        }
    }

    /**
     * @param bool $wait
     * @dataProvider getTrueFalse
     */
    public function testStopAll($wait = null)
    {
        if(is_null($wait))
        {
            $this->process->expects($this->exactly(1))
                ->method('stopProcess')
                ->with(true);

            $this->subject->stopAll();
        }
        else
        {
            $this->process->expects($this->exactly(1))
                ->method('stopProcess')
                ->with($wait);

            $this->subject->stopAll($wait);
        }
    }

    /**
     * @return array
     */
    public static function getTrueFalse()
    {
        return [
            [],
            [true],
            [false]
        ];
    }

    /**
     * @return AgentInterface
     */
    protected function getAgent()
    {
        $agents = $this->subject->findAll();

        /** @var AgentInterface $agent */
        return array_pop($agents);
    }
}
