<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Logger\Handler;

use Abc\Bundle\JobBundle\Job\JobAwareInterface;
use Abc\Bundle\JobBundle\Job\JobInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobAwareOrmHandler extends OrmHandler implements JobAwareInterface
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * {@inheritdoc}
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * Sets the job ticket in $record['extra']['job_ticket']
     *
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $log = $this->manager->create();

        $record['extra']['job_ticket'] = $this->job->getTicket();

        $this->populateLog($log, $record);

        $this->manager->save($log);
    }
}