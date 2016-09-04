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
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Logger\Handler\HandlerFactoryRegistry;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var HandlerFactoryRegistry
     */
    protected $handlerFactory;

    /**
     * @var array|HandlerInterface[]
     */
    protected $handlers = array();

    /**
     * @param JobTypeRegistry        $registry
     * @param HandlerFactoryRegistry $handlerFactory
     */
    public function __construct(JobTypeRegistry $registry, HandlerFactoryRegistry $handlerFactory)
    {
        $this->registry       = $registry;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(JobInterface $job)
    {
        $level = $this->registry->get($job->getType())->getLogLevel();

        if (false === $level) {
            return new NullLogger();
        }

        $handlers = $this->handlerFactory->createHandlers($job, $level);

        return new Logger($this->buildChannel($job), array_merge($handlers, $this->handlers));
    }

    /**
     * @param HandlerInterface $handler
     * @return void
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param JobInterface $job
     * @return string The channel name
     */
    protected function buildChannel(JobInterface $job)
    {
        return $job->getTicket();
    }
}