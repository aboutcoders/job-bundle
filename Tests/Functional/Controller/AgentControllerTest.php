<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Controller;

use Abc\Bundle\JobBundle\Model\Agent;
use Abc\Bundle\JobBundle\Model\AgentManagerInterface;
use Abc\Bundle\JobBundle\Test\WebTestCase;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AgentControllerTest extends WebTestCase
{
    /**
     * @var AgentManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $agentManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->agentManager = $this->getMock(AgentManagerInterface::class);
        $this->serializer   = SerializerBuilder::create()->build();
    }

    public function testListAction()
    {
        $client = static::createClient();

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('PROCESSING');

        $this->agentManager->expects($this->once())
            ->method('findAll')
            ->willReturn([$agent]);

        $this->mockServices(['abc.job.agent_manager' => $this->agentManager]);

        $crawler = $client->request('GET', '/api/agents');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        $deserializedArray = $this->serializer->deserialize($data, sprintf('ArrayCollection<%s>', Agent::class), 'json');

        $this->assertEquals([$agent], $deserializedArray);
    }

    public function testListActionReturns200()
    {
        $client = static::createClient();

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('PROCESSING');

        $this->agentManager->expects($this->once())
            ->method('findById')
            ->willReturn($agent);

        $this->mockServices(['abc.job.agent_manager' => $this->agentManager]);

        $crawler = $client->request('GET', '/api/agents/12345');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        $deserializedObject = $this->serializer->deserialize($data, Agent::class, 'json');

        $this->assertEquals($agent, $deserializedObject);
    }

    public function testGetActionReturns404()
    {
        $client = static::createClient();

        $this->agentManager->expects($this->any())
            ->method('findById')
            ->willReturn(null);

        $this->mockServices(['abc.job.agent_manager' => $this->agentManager]);

        $crawler = $client->request('GET', '/api/agents/12345');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testStartAction()
    {
        $client = static::createClient();

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('STOPPED');

        $this->agentManager->expects($this->atLeastOnce())
            ->method('findById')
            ->with('12345')
            ->willReturn($agent);

        $this->agentManager->expects($this->atLeastOnce())
            ->method('start')
            ->with($agent);

        $this->agentManager->expects($this->atLeastOnce())
            ->method('refresh')
            ->with($agent);

        $this->mockServices(['abc.job.agent_manager' => $this->agentManager]);

        $client->request('POST', '/api/agents/12345/start');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        // would be nice to somehow ensure that refreshed object is returned, however using returnCallback somehow returns a cloned object
        $deserializedObject = $this->serializer->deserialize($data, Agent::class, 'json');

        $this->assertEquals($agent, $deserializedObject);
    }

    public function testStopAction()
    {
        $client = static::createClient();

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('STOPPED');

        $this->agentManager->expects($this->atLeastOnce())
            ->method('findById')
            ->with('12345')
            ->willReturn($agent);

        $this->agentManager->expects($this->atLeastOnce())
            ->method('stop')
            ->with($agent);

        $this->agentManager->expects($this->atLeastOnce())
            ->method('refresh')
            ->with($agent);

        $this->mockServices(['abc.job.agent_manager' => $this->agentManager]);

        $client->request('POST', '/api/agents/12345/stop');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        // would be nice to somehow ensure that refreshed object is returned, however using returnCallback somehow returns a cloned object
        $deserializedObject = $this->serializer->deserialize($data, Agent::class, 'json');

        $this->assertEquals($agent, $deserializedObject);
    }
}