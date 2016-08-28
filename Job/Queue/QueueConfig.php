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
class QueueConfig implements QueueConfigInterface
{
    /**
     * @var array
     */
    private $map;

    /**
     * @var string
     */
    private $defaultQueue;

    /**
     * @param array  $queueMapping
     * @param string $defaultQueue
     */
    public function __construct(array $queueMapping = [], $defaultQueue = 'default')
    {
        $this->defaultQueue = $defaultQueue;
        foreach ($queueMapping as $queueName => $jobTypes) {
            foreach ($jobTypes as $jobType) {
                if(!isset($this->map[$jobType])) {
                    $this->map[$jobType] = $queueName;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultQueue()
    {
        return $this->defaultQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue($type)
    {
        return isset($this->map[$type]) ? $this->map[$type] : $this->defaultQueue;
    }
}