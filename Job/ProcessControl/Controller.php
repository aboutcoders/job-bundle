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

use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\ProcessControl\ControllerInterface;

/**
 * The default job process controller.
 *
 * This controller uses the configured manager (service id: abc.job.manager)
 * to refresh the job entity and to check whether the job status is CANCELLED
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Controller implements ControllerInterface
{
    /**
     * @var JobManagerInterface
     */
    protected $manager;

    /**
     * @var
     */
    protected $interval;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var integer
     */
    private $lastCheck;

    /**
     * @param JobInterface        $job
     * @param JobManagerInterface $manager
     * @param integer             $interval The minimum number of seconds that must have been passed between two refresh operations
     * @throws \InvalidArgumentException If interval is not greater than of equal to zero
     */
    public function __construct(JobInterface $job, JobManagerInterface $manager, $interval)
    {
        if((int) $interval < 0) {
            throw new \InvalidArgumentException('$interval must be greater than or equal to zero');
        }

        $this->job = $job;
        $this->manager  = $manager;
        $this->interval = $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function doExit()
    {
        $time = time();
        if(null === $this->lastCheck || (($this->lastCheck + $this->interval) <= $time))
        {
            $this->lastCheck = $time;
            $this->manager->refresh($this->job);
        }

        return $this->job->getStatus() == Status::CANCELLED();
    }
}