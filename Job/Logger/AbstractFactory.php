<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Logger;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\NullLogger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var bool
     */
    protected $bubble;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $processors = array();

    /**
     * @param JobTypeRegistry         $registry
     * @param FormatterInterface|null $formatter Optional, the formatter to use for the created loggers
     * @param array                   $processors Processors pushed to the handler
     * @param bool                    $bubble defaults to true
     */
    public function __construct(JobTypeRegistry $registry, FormatterInterface $formatter = null, array $processors = array(), $bubble = true)
    {
        foreach($processors as $callable)
        {
            $this->addProcessor($callable);
        }

        $this->formatter = $formatter;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(JobInterface $job)
    {
        $level = $this->getLogLevel($job->getType());

        if(null == $level)
        {
            return new NullLogger();
        }

        $handler = $this->createHandler($job, $level, $this->bubble);

        if($this->formatter != null)
        {
            $handler->setFormatter($this->formatter);
        }

        foreach($this->processors as $processor)
        {
            $handler->pushProcessor($processor);
        }

        return new Logger($this->buildChannel($job), array($handler));
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function addProcessor($callable)
    {
        if(!is_callable($callable))
        {
            throw new \InvalidArgumentException('Processor must be callable');
        }

        $this->processors[] = $callable;
    }

    /**
     * @param JobInterface $job
     * @param int          $level
     * @param bool         $bubble
     * @return HandlerInterface|null
     */
    protected abstract function createHandler(JobInterface $job, $level = Logger::DEBUG, $bubble = true);

    /**
     * @param JobInterface $job
     * @return string The channel name
     */
    protected function buildChannel(JobInterface $job)
    {
        return '';
    }

    /**
     * @param string $type The job type
     * @return int|null
     */
    protected function getLogLevel($type)
    {
        return $this->registry->get($type)->getLogLevel();
    }
}