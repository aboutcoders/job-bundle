<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface LogManagerInterface
{
    /**
     * @param JobInterface $job
     * @return string|null The logs of a job (null if no logs are found)
     */
    public function findByJob(JobInterface $job);

    /**
     * @param JobInterface $job
     * @return void
     * @throws \RuntimeException If deletion fails
     */
    public function deleteByJob(JobInterface $job);
}