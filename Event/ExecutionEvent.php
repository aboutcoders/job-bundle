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

use Abc\Bundle\JobBundle\Job\Context\ContextInterface;
use Abc\Bundle\JobBundle\Job\JobInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ExecutionEvent extends TerminationEvent
{

    /** @var ContextInterface */
    protected $context;

    /**
     * @param JobInterface     $job
     * @param ContextInterface $context
     */
    function __construct(JobInterface $job, ContextInterface $context)
    {
        parent::__construct($job);

        $this->context = $context;
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}