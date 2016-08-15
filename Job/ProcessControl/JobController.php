<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\ProcessControl;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\ProcessControl\ControllerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobController implements ControllerInterface
{
    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var \Abc\Bundle\JobBundle\Model\JobInterface
     */
    private $job;

    /**
     * @param ControllerInterface $controller
     * @param JobManagerInterface $jobManager
     * @param JobInterface        $job
     * @throws \InvalidArgumentException If the given manager is not a manager of the given job
     */
    public function __construct(ControllerInterface $controller, JobManagerInterface $jobManager, JobInterface $job)
    {
        if(!$jobManager->isManagerOf($job)) {
            throw new \InvalidArgumentException('The job manager is not a manager of the job');
        }

        $this->controller = $controller;
        $this->jobManager = $jobManager;
        $this->job        = $job;
    }

    /**
     * Sets status of the job to cancelled if process controller indicates to exit.
     *
     * @return boolean
     */
    public function doExit()
    {
        if ($this->controller->doExit()) {
            $this->job->setStatus(Status::CANCELLED());

            return true;
        }

        return false;
    }
}