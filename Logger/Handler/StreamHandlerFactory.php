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
use Monolog\Handler\StreamHandler;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StreamHandlerFactory extends BaseHandlerFactory
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @param int    $level
     * @param bool   $bubble
     * @param string $path
     * @param string $extension
     */
    public function __construct($level, $bubble, $path, $extension = 'log')
    {
        parent::__construct($level, $bubble);
        $this->path      = $path;
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function createHandler(JobInterface $job, $level = null)
    {
        $handler = new StreamHandler($this->buildPath($job->getTicket()), $level == null ? $this->level : $level, $this->bubble);

        return $this->initHandler($handler);
    }

    /**
     * @param string $filename
     * @return string The path of the logfile
     */
    private function buildPath($filename)
    {
        return $this->path . DIRECTORY_SEPARATOR . $filename . '.' . $this->extension;
    }
}