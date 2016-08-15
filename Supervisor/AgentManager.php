<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Supervisor;

use Abc\Bundle\JobBundle\Model\Agent;
use Abc\Bundle\JobBundle\Model\AgentInterface;
use Abc\Bundle\JobBundle\Model\AgentManagerInterface;
use Supervisor\Process;
use Supervisor\Supervisor;
use YZ\SupervisorBundle\Manager\SupervisorManager;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AgentManager implements AgentManagerInterface
{
    /**
     * @var SupervisorManager
     */
    protected $supervisorManager;

    /**
     * @var array
     */
    private $agents;

    /**
     * @var array
     */
    private $processes;

    /**
     * @var array
     */
    private static $stateToStringMap = [
        0 => 'STOPPED',
        10 => 'STARTING',
        20 => 'RUNNING',
        30 => 'BACKOFF',
        40 => 'STOPPING',
        100 => 'EXITED',
        200 => 'FATAL',
        1000 => 'UNKNOWN',
    ];

    /**
     * @param SupervisorManager $supervisorManager
     */
    public function __construct(SupervisorManager $supervisorManager)
    {
        $this->supervisorManager = $supervisorManager;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(AgentInterface $agent)
    {
        $process      = $this->getProcess($agent);

        $managedAgent = $this->agents[$agent->getId()];

        $this->updateObject($managedAgent, $process);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->agents = null;
        $this->processes = null;
    }

    /**
     * {@inheritdoc}
     */
    public function start(AgentInterface $agent, $wait = true)
    {
        $this->getProcess($agent)->startProcess($wait);
    }

    /**
     * {@inheritdoc}
     */
    public function stop(AgentInterface $agent, $wait = true)
    {
        $this->getProcess($agent)->stopProcess($wait);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        if(is_null($this->agents))
        {
            $this->findAll();
        }

        return isset($this->agents[$id]) ? $this->agents[$id] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        if(is_null($this->agents))
        {
            $this->agents = array();
            $this->processes = array();

            foreach($this->supervisorManager->getSupervisors() as $supervisor)
            {
                /** @var Supervisor $supervisor */
                foreach($supervisor->getProcesses() as $process)
                {
                    $agent = new Agent();
                    $agent->setId($process->getGroup() . ':' . $process->getName());
                    $agent->setName($process->getName());

                    $this->updateObject($agent, $process);

                    $this->agents[$agent->getId()]    = $agent;
                    $this->processes[$agent->getId()] = $process;
                }
            }
        }

        return array_values($this->agents);
    }

    /**
     * {@inheritdoc}
     */
    public function startAll($wait = true)
    {
        if(is_null($this->agents))
        {
            $this->findAll();
        }

        foreach(array_values($this->processes) as $process)
        {
            /** @var Process $process */
            $process->startProcess($wait);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopAll($wait = true)
    {
        if(is_null($this->agents))
        {
            $this->findAll();
        }

        foreach(array_values($this->processes) as $process)
        {
            /** @var Process $process */
            $process->stopProcess($wait);
        }
    }

    /**
     * @param AgentInterface $agent
     * @return Process|null
     */
    protected function getProcess(AgentInterface $agent)
    {
        return isset($this->processes[$agent->getId()]) ? $this->processes[$agent->getId()] : null;
    }

    /**
     * @param AgentInterface $agent
     * @param Process        $process
     * @return void
     */
    protected function updateObject(AgentInterface $agent, Process $process)
    {
        /** @var Process $process */
        $processInfo = $process->getProcessInfo();

        $agent->setStatus(static::$stateToStringMap[$processInfo['state']]);
    }
}