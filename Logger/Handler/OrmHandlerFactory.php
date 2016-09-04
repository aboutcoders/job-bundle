<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Logger\Handler;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class OrmHandlerFactory extends BaseHandlerFactory
{
    /**
     * @var LogManagerInterface
     */
    protected $manager;

    /**
     * @param int                 $level
     * @param bool                $bubble
     * @param LogManagerInterface $manager
     *
     */
    public function __construct($level, $bubble, LogManagerInterface $manager)
    {
        parent::__construct($level, $bubble);
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function createHandler(JobInterface $job, $level = null)
    {
        $handler = new JobAwareOrmHandler($this->manager, $level == null ? $this->level : $level, $this->bubble);
        $handler->setJob($job);

        return $this->initHandler($handler);
    }
}