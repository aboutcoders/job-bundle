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

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
final class JobEvents
{

    /**
     * The abc.job.message_consume event is triggered each time a message is consumed from the queue
     *
     * The event listener receives an event of type Symfony\Component\EventDispatcher\Event
     *
     * @var string
     */
    const JOB_MESSAGE_CONSUME = 'abc.job.message_consume';

    /**
     * The abc.job.pre_execute event is triggered each time before a job is executed
     *
     * The event listener receives an event of type Abc\Bundle\JobBundle\Event\ExecutionEvent
     *
     * @var string
     */
    const JOB_PRE_EXECUTE = 'abc.job.pre_execute';

    /**
     * The abc.job.post_execute event is triggered each time before a job is executed
     *
     * The event listener receives an event of type Abc\Bundle\JobBundle\Event\ExecutionEvent
     *
     * @var string
     */
    const JOB_POST_EXECUTE = 'abc.job.post_execute';

    /**
     * The abc.job.terminated event is triggered whenever a root job terminates
     *
     *  The event listener receives an event of type Abc\Bundle\JobBundle\Event\TerminationEvent
     *
     * @var string
     */
    const JOB_TERMINATED = 'abc.job.terminated';
} 