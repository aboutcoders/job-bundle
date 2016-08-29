<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Queue;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ConsumerInterface
{
    /**
     * Consumes messages from the queue.
     *
     * @param string $queue The name of the queue
     * @param array  $options
     * @return void
     */
    public function consume($queue, array $options = []);
}