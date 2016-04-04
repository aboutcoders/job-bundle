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
 * Defines a queue engine where messages can be pushed to.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface QueueEngineInterface
{
    /**
     * Publishes a message to the backend.
     *
     * @param Message $message
     * @return void
     * @throws \RuntimeException If publishing fails
     */
    public function publish(Message $message);
}