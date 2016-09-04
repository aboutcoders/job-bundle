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
use Abc\Bundle\SchedulerBundle\Schedule\ProcessorInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class BaseHandlerFactory
{
    /**
     * @var int
     */
    protected $level;

    /**
     * @var boolean
     */
    protected $bubble;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var array|ProcessorInterface[]
     */
    protected $processors = array();

    /**
     * @param int  $level
     * @param bool $bubble
     */
    public function __construct($level, $bubble)
    {
        $this->level  = $level;
        $this->bubble = $bubble;
    }

    /**
     * @param JobInterface $job
     * @param int|null     $level The minimum logging level at which this handler will be triggered
     * @return HandlerInterface
     */
    public abstract function createHandler(JobInterface $job, $level = null);

    /**
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param array|ProcessorInterface[] $processors
     */
    public function setProcessors(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @param HandlerInterface $handler
     * @return HandlerInterface
     */
    protected function initHandler(HandlerInterface $handler)
    {
        if($this->formatter != null) {
            $handler->setFormatter($this->formatter);
        }

        foreach ($this->processors as $processor) {
            $handler->pushProcessor($processor);
        }

        return $handler;
    }
}