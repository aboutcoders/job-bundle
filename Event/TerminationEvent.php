<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Event;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TerminationEvent extends Event
{
    /** @var JobInterface */
    protected $job;

    /**
     * @param JobInterface $job
     */
    function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * @return JobInterface
     */
    public function getJob()
    {
        return $this->job;
    }
}