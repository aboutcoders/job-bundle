<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Logger;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Psr\Log\LoggerInterface;

/**
 * Factory for loggers and logs for jobs.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface LoggerFactoryInterface
{
    /**
     * @param JobInterface $job
     * @return LoggerInterface
     */
    public function create(JobInterface $job);
}