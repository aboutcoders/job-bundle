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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AgentController extends BaseController
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
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        return $this->serialize($this->getAgentManager()->findAll());
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

        return $this->serialize($agent);
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
     * @param string $id
     * @param boolean $wait Whether to wait until agent is started (optional, true by default)
     * @return Response
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

        return $this->serialize($agent);
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
     * @param int $id
     * @param boolean $wait Whether to wait until agent is stopped (optional, true by default)
     * @return Response
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

        return $this->serialize($agent);
    }

    /**
     * @return AgentManagerInterface
     */
    private function getAgentManager()
    {
        return $this->get('abc.job.agent_manager');
    }
}