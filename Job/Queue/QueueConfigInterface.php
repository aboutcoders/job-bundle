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
interface QueueConfigInterface
{
    /**
     * @return string The name of the default queue
     */
    public function getDefaultQueue();

    /**
     * Returns the name of a queue of a job.
     * 
     * @param string $type The job type
     * @return string The queue name
     */
    public function getQueue($type);
}