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

use Abc\Bundle\JobBundle\Api\BadRequestResponse;
use Abc\Bundle\JobBundle\Controller\JobController;
use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Test\ControllerTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobControllerTest extends ControllerTestCase
{
    /**
     * @var JobController
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->subject = new JobController();
        $this->subject->setContainer($this->container);
    }

    public function testGetAction()
    {
        $job = new Job();

        $this->manager->expects($this->once())
            ->method('get')
            ->with('JobTicket')
            ->willReturn($job);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($job, 'json')
            ->willReturn('data');

        $response = $this->subject->getAction('JobTicket');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testGetActionReturns404()
    {
        $exception = new TicketNotFoundException('JobTicket');

        $this->manager->expects($this->once())
            ->method('get')
            ->with('JobTicket')
            ->willThrowException($exception);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo(new BadRequestResponse('Not found', $exception->getMessage())), 'json')
            ->willReturn('data');

        $response = $this->subject->getAction('JobTicket');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testAddAction()
    {
        $parameters = ['type' => 'JobType'];
        $job        = new Job();
        $addedJob   = new Job();
        $request    = new Request([], $parameters);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($parameters, true), Job::class, 'json')
            ->willReturn($job);

        $this->manager->expects($this->once())
            ->method('add')
            ->with($job)
            ->willReturn($addedJob);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($addedJob, 'json')
            ->willReturn('data');

        $this->validator->expects($this->never())
            ->method('validate');

        $response = $this->subject->addAction($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAddActionWithValidationEnabled()
    {
        $parameters = ['type' => 'JobType'];
        $job        = new Job();
        $request    = new Request([], $parameters);

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with('abc.job.rest.validate')
            ->willReturn(true);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($parameters, true), Job::class, 'json')
            ->willReturn($job);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($job)
            ->willReturn(['error']);

        $expectedResponse = new BadRequestResponse('Invalid request', 'The request contains invalid job parameters');
        $expectedResponse->setErrors(['error']);

        $this->manager->expects($this->never())
            ->method('add');

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($expectedResponse), 'json')
            ->willReturn('data');

        $response = $this->subject->addAction($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateAction()
    {
        $parameters = ['type' => 'JobType'];
        $job        = new Job();
        $updatedJob = new Job();
        $request    = new Request([], $parameters);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($parameters, true), Job::class, 'json')
            ->willReturn($job);

        $this->manager->expects($this->once())
            ->method('update')
            ->with($job)
            ->willReturn($updatedJob);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($updatedJob, 'json')
            ->willReturn('data');

        $this->validator->expects($this->never())
            ->method('validate');

        $response = $this->subject->updateAction($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateActionWithValidationEnabled()
    {
        $parameters = ['type' => 'JobType'];
        $job        = new Job();
        $request    = new Request([], $parameters);

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with('abc.job.rest.validate')
            ->willReturn(true);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($parameters, true), Job::class, 'json')
            ->willReturn($job);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($job)
            ->willReturn(['error']);

        $expectedResponse = new BadRequestResponse('Invalid request', 'The request contains invalid job parameters');
        $expectedResponse->setErrors(['error']);

        $this->manager->expects($this->never())
            ->method('add');

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($expectedResponse), 'json')
            ->willReturn('data');

        $response = $this->subject->addAction($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCancelAction()
    {
        $job = new Job();

        $this->manager->expects($this->once())
            ->method('cancel')
            ->with('JobTicket')
            ->willReturn($job);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($job, 'json')
            ->willReturn('data');

        $response = $this->subject->cancelAction('JobTicket');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testCancelActionReturns404()
    {
        $exception = new TicketNotFoundException('JobTicket');

        $this->manager->expects($this->once())
            ->method('cancel')
            ->with('JobTicket')
            ->willThrowException($exception);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo(new BadRequestResponse('Not found', $exception->getMessage())), 'json')
            ->willReturn('data');

        $response = $this->subject->cancelAction('JobTicket');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testRestartAction()
    {
        $job = new Job();

        $this->manager->expects($this->once())
            ->method('restart')
            ->with('JobTicket')
            ->willReturn($job);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($job, 'json')
            ->willReturn('data');

        $response = $this->subject->restartAction('JobTicket');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testRestartActionReturns404()
    {
        $exception = new TicketNotFoundException('JobTicket');

        $this->manager->expects($this->once())
            ->method('getLogs')
            ->with('JobTicket')
            ->willThrowException($exception);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo(new BadRequestResponse('Not found', $exception->getMessage())), 'json')
            ->willReturn('data');

        $response = $this->subject->logsAction('JobTicket');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testLogsAction()
    {
        $job = new Job();

        $this->manager->expects($this->once())
            ->method('getLogs')
            ->with('JobTicket')
            ->willReturn($job);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($job, 'json')
            ->willReturn('data');

        $response = $this->subject->logsAction('JobTicket');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }

    public function testLogsActionReturns404()
    {
        $exception = new TicketNotFoundException('JobTicket');

        $this->manager->expects($this->once())
            ->method('restart')
            ->with('JobTicket')
            ->willThrowException($exception);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo(new BadRequestResponse('Not found', $exception->getMessage())), 'json')
            ->willReturn('data');

        $response = $this->subject->restartAction('JobTicket');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('data', $response->getContent());
    }
}