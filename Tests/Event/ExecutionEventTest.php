<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Event;

use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Model\Job;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ExecutionEventTest extends TerminationEventTest
{
    public function testGetContext()
    {
        $job = new Job();
        $context = new Context();

        $event = new ExecutionEvent($job, $context);

        $this->assertSame($job, $event->getJob());
        $this->assertSame($context, $event->getContext());
    }
}
