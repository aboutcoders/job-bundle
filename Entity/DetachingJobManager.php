<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Entity;

use Abc\Bundle\JobBundle\Model\JobInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class DetatchingJobManager extends JobManager
{
    /**
     * @var JobInterface
     */
    private $current;

    /**
     * @inheritdoc
     */
    public function save(JobInterface $job, $andFlush = true)
    {
        $this->objectManager->persist($job);
        if ($andFlush) {
            $this->objectManager->flush();
            if ($this->current != null && $this->current !== $job) {
                $this->objectManager->detach($this->current);
            }
            $this->current = $job;
        }
    }
}