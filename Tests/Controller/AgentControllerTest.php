<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Controller;

use Abc\Bundle\JobBundle\Model\Agent;
use Abc\Bundle\JobBundle\Model\AgentManagerInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AgentControllerTest extends WebTestCase
{

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function testCgetAction()
    {
        $client = static::createClient();

        /** @var AgentManagerInterface|\PHPUnit_Framework_MockObject_MockObject $agentManager */
        $agentManager = $client->getContainer()->get('abc.job.agent_manager');


        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('PROCESSING');

        $agentManager->expects($this->once())
            ->method('findAll')
            ->willReturn([$agent]);

        $crawler = $client->request('GET', '/api/agents');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        $deserializedArray = $this->serializer->deserialize($data, 'ArrayCollection<Abc\Bundle\JobBundle\Model\Agent>', 'json');

        $this->assertEquals([$agent], $deserializedArray);
    }

    public function testGetActionReturns200()
    {
        $client = static::createClient();

        /** @var AgentManagerInterface|\PHPUnit_Framework_MockObject_MockObject $agentManager */
        $agentManager = $client->getContainer()->get('abc.job.agent_manager');

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('PROCESSING');

        $agentManager->expects($this->once())
            ->method('findById')
            ->willReturn($agent);

        $crawler = $client->request('GET', '/api/agents/12345');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        $deserializedObject = $this->serializer->deserialize($data, 'Abc\Bundle\JobBundle\Model\Agent', 'json');

        $this->assertEquals($agent, $deserializedObject);
    }

    public function testGetActionReturns404()
    {
        $client = static::createClient();

        /** @var AgentManagerInterface|\PHPUnit_Framework_MockObject_MockObject $agentManager */
        $agentManager = $client->getContainer()->get('abc.job.agent_manager');

        $agentManager->expects($this->any())
            ->method('findById')
            ->willReturn(null);

        $crawler = $client->request('GET', '/api/agents/12345');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testStartAction()
    {
        $client = static::createClient();

        /** @var AgentManagerInterface|\PHPUnit_Framework_MockObject_MockObject $agentManager */
        $agentManager = $client->getContainer()->get('abc.job.agent_manager');

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('STOPPED');

        $agentManager->expects($this->atLeastOnce())
            ->method('findById')
            ->with('12345')
            ->willReturn($agent);

        $agentManager->expects($this->atLeastOnce())
            ->method('start')
            ->with($agent);

        $agentManager->expects($this->atLeastOnce())
            ->method('refresh')
            ->with($agent);

        $client->request('PUT', '/api/agents/12345/start');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        // would be nice to somehow ensure that refreshed object is returned, however using returnCallback somehow returns a cloned object
        $deserializedObject = $this->serializer->deserialize($data, 'Abc\Bundle\JobBundle\Model\Agent', 'json');

        $this->assertEquals($agent, $deserializedObject);
    }

    public function testStopAction()
    {
        $client = static::createClient();

        /** @var AgentManagerInterface|\PHPUnit_Framework_MockObject_MockObject $agentManager */
        $agentManager = $client->getContainer()->get('abc.job.agent_manager');

        $agent = new Agent();
        $agent->setId('id');
        $agent->setName('Foobar');
        $agent->setStatus('STOPPED');

        $agentManager->expects($this->atLeastOnce())
            ->method('findById')
            ->with('12345')
            ->willReturn($agent);

        $agentManager->expects($this->atLeastOnce())
            ->method('stop')
            ->with($agent);

        $agentManager->expects($this->atLeastOnce())
            ->method('refresh')
            ->with($agent);

        $client->request('PUT', '/api/agents/12345/stop');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = $client->getResponse()->getContent();

        // would be nice to somehow ensure that refreshed object is returned, however using returnCallback somehow returns a cloned object
        $deserializedObject = $this->serializer->deserialize($data, 'Abc\Bundle\JobBundle\Model\Agent', 'json');

        $this->assertEquals($agent, $deserializedObject);
    }
}