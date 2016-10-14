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

use Abc\Bundle\JobBundle\Job\ManagerAwareInterface;

/**
 * Sends messages to a queue backend.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ProducerInterface extends ManagerAwareInterface
{
    /**
     * Sends a message to the queue backend.
     *
     * @param Message $message
     * @return void
     * @throws \RuntimeException If publishing fails
     */
    public function produce(Message $message);
}