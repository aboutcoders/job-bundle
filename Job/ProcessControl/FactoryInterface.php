<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\ProcessControl;

use Abc\Bundle\JobBundle\Job\JobAwareInterface;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\ProcessControl\Controller as ControllerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface FactoryInterface
{
    /**
     * Creates the controller passed to a job implementing the
     *
     * @param JobInterface $job
     * @return ControllerInterface
     * @see JobAwareInterface
     */
    public function create(JobInterface $job);
}