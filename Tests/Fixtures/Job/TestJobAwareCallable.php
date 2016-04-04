<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Fixtures\Job;

use Abc\Bundle\JobBundle\Job\Job;
use Abc\Bundle\JobBundle\Job\JobAwareInterface;
use Abc\Bundle\JobBundle\Job\JobInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TestJobAwareCallable implements JobAwareInterface
{
    /** @var JobInterface */
    protected $job;

    public static function getMethodName()
    {
        return 'execute';
    }

    /**
     * @param mixed $job
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * @return JobInterface
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @return string
     */
    public function execute()
    {
        return 'foobar';
    }
}