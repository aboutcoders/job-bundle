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
use Monolog\Handler\HandlerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class HandlerFactory
{
    /**
     * @var array|BaseHandlerFactory[]
     */
    private $factories = [];

    /**
     * @param BaseHandlerFactory $factory
     */
    public function addFactory(BaseHandlerFactory $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * @param JobInterface $job
     * @param int|null     $level The minimum logging level at which this handler will be triggered
     * @return array|HandlerInterface[]
     */
    public function createHandlers(JobInterface $job, $level = null)
    {
        $handlers = [];
        foreach ($this->factories as $factory) {
            $handlers[] = $factory->createHandler($job, $level);
        }

        return $handlers;
    }
}