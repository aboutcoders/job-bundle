<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Logger\Factory;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * A Logger factory that creates loggers for jobs based on stream handlers.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class FileLoggerFactory extends AbstractFactory
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @param JobTypeRegistry         $registry
     * @param string                  $directory Path to a directory where log files for jobs are stored
     * @param array                   $processors Processors pushed to the handler
     * @throws \InvalidArgumentException If $directory does not specify a path to a writable directory
     * @throws \InvalidArgumentException If $processors contains elements that are not a callable
     */
    public function __construct(JobTypeRegistry $registry, $directory, array $processors = array())
    {
        parent::__construct($registry, $processors);

        if(!is_string($directory) || !is_dir($directory) || !is_writable($directory))
        {
            throw new \InvalidArgumentException('$directory must be a string specifying the path to a writable directory');
        }

        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    protected function createHandler(JobInterface $job, $level = Logger::DEBUG, $bubble = true)
    {
        $handler = new StreamHandler($this->buildPath($job->getTicket()), $level);
        $handler->setFormatter(new JsonFormatter());

        return $handler;
    }

    /**
     * @param string $filename
     * @return string Path to the file
     */
    private function buildPath($filename)
    {
        return $this->directory . DIRECTORY_SEPARATOR . $filename . '.log';
    }
}