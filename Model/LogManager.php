<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Model;

use Abc\Bundle\JobBundle\Job\JobInterface as BaseJobInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class LogManager implements LogManagerInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $class = $this->getClass();

        /** @var LogInterface $log */
        return new $class;
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
    public function getFormatter()
    {
        if(!$this->formatter)
        {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    /**
     * Gets the default formatter.
     *
     * @return FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter();
    }

    /**
     * Formats an array of Log[] items using the formatter
     *
     * @param Log[] $logs
     * @return string
     * @see getFormatter
     */
    protected function formatLogs($logs)
    {
        $string = '';
        foreach($logs as $log)
        {
            $string .= $this->getFormatter()->format($log->toRecord());
        }

        return $string;
    }

    /**
     * Formats an array of Log[] items using the formatter
     *
     * @param Log[] $logs
     * @return int the number of delete entries
     */
    protected function deleteLogs($logs)
    {
        $i = 0;
        foreach($logs as $log)
        {
            $this->delete($log);
            $i++;
        }

        return $i;
    }
}