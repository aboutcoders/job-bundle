<?php

namespace Abc\JobBundle\Controller;

use Abc\Job\Job;
use Abc\Job\Model\ScheduleManagerInterface;
use Abc\Job\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{
    /**
     * @var ScheduleManagerInterface
     */
    private $scheduleManager;

    public function __construct(ScheduleManagerInterface $scheduleManager)
    {
        $this->scheduleManager = $scheduleManager;
    }

    /**
     * @Route("/schedule", methods="POST")
     *
     * @param Request $request
     * @return Response
     */
    public function process(Request $request)
    {
        $job = Job::fromArray([
            'type' => Type::JOB(),
            'name' => 'JobWorkerA',
        ]);

        $schedule = $this->scheduleManager->create('* * * * *', $job->toJson());

        $this->scheduleManager->save($schedule);

        return new JsonResponse();
    }
}
