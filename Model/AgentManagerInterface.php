<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Model;


/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface AgentManagerInterface
{

    /**
     * Refreshes the persistent state of an agent from an external API
     *
     * @param AgentInterface $agent
     * @return mixed
     */
    public function refresh(AgentInterface $agent);

    /**
     * @param AgentInterface $agent
     * @param bool           $wait Whether to wait until process is started (optional, true by default)
     * @return void
     */
    public function start(AgentInterface $agent, $wait = true);

    /**
     * @param AgentInterface $agent
     * @param bool           $wait Whether to wait until process is stopped (optional, true by default)
     * @return void
     */
    public function stop(AgentInterface $agent, $wait = true);

    /**
     * @param $id
     * @return AgentInterface|null
     */
    public function findById($id);

    /**
     * Finds all agents
     *
     * @return array
     */
    public function findAll();

    /**
     * @param bool $wait Whether to wait until all processes are started (optional, true by default)
     * @return void
     */
    public function startAll($wait = true);

    /**
     * @param bool $wait Whether to wait until all processes are stopped (optional, true by default)
     * @return void
     */
    public function stopAll($wait = true);
}