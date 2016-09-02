<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Controller;

use Abc\Bundle\JobBundle\Model\AgentInterface;
use Abc\Bundle\JobBundle\Model\AgentManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @RouteResource("Agent")
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AgentController extends FOSRestController
{
    /**
     * @ApiDoc(
     * description="Returns a collection of agents",
     * section="AbcJobBundle",
     * output="array<Abc\Bundle\JobBundle\Model\Agent>",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @return array data
     */
    public function cgetAction()
    {
        return $this->getAgentManager()->findAll();
    }

    /**
     * @ApiDoc(
     * description="Returns an agent",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Agent",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when agent not found",
     *   }
     * )
     *
     * @param string $id The agent id
     * @return AgentInterface
     */
    public function getAction($id)
    {
        $agent = $this->getAgentManager()->findById($id);

        if (!$agent) {
            throw $this->createNotFoundException('Unable to find agent');
        }

        return $agent;
    }

    /**
     * @ApiDoc(
     * description="Starts an agent",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Agent",
     * parameters={
     *      {"name"="wait", "dataType"="boolean", "required"=false, "description"="Whether to wait until agent is started"}
     * },
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when agent not found",
     *   }
     * )
     *
     * @Post
     *
     * @param string $id
     * @param boolean $wait Whether to wait until agent is started (optional, true by default)
     * @return AgentInterface
     */
    public function startAction($id, $wait = true)
    {
        $manager = $this->getAgentManager();

        $agent = $manager->findById($id);

        if(is_null($agent))
        {
            throw $this->createNotFoundException(sprintf('Agent with id %s not found', $id));
        }

        $manager->start($agent, $wait);
        $manager->refresh($agent);

        return $agent;
    }

    /**
     * @ApiDoc(
     * description="Stops an agent",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Agent",
     * parameters={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="The agent id"},
     *      {"name"="wait", "dataType"="boolean", "required"=false, "description"="Whether to wait until agent is stopped"}
     * },
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when agent not found",
     *   }
     * )
     *
     * @Post
     *
     * @param int $id
     * @param boolean $wait Whether to wait until agent is stopped (optional, true by default)
     * @return AgentInterface
     */
    public function stopAction($id, $wait = true)
    {
        $manager = $this->getAgentManager();

        $agent = $manager->findById($id);

        if(is_null($agent))
        {
            throw $this->createNotFoundException('Unable to find Node entity.');
        }

        $manager->stop($agent, $wait);
        $manager->refresh($agent);

        return $agent;
    }

    /**
     * @return AgentManagerInterface
     */
    private function getAgentManager()
    {
        return $this->get('abc.job.agent_manager');
    }
}