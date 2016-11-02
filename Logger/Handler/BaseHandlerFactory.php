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
abstract class BaseHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var array|ProcessorInterface[]
     */
    protected $processors = array();

    /**
     * {@inheritdoc}
     */
    public abstract function createHandler(JobInterface $job, $level, $bubble);

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessors(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * Sets formatter and processors.
     *
     * @param HandlerInterface $handler
     * @return HandlerInterface
     */
    protected function initHandler(HandlerInterface $handler)
    {
        if ($this->formatter != null) {
            $handler->setFormatter($this->formatter);
        }

        foreach ($this->processors as $processor) {
            $handler->pushProcessor($processor);
        }

        return $handler;
    }
}