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
use Abc\Bundle\JobBundle\Job\LogManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StreamLogManager implements LogManagerInterface
{

    /** @var string */
    protected $directory;

    /**
     * @param string $directory Path to a directory where log files for jobs are stored
     * @throws \InvalidArgumentException If $directory does not specify a path to a writable directory
     * @throws \InvalidArgumentException If $processors contains elements that are not a callable
     */
    public function __construct($directory)
    {
        if(!is_string($directory) || !is_dir($directory) || !is_writable($directory))
        {
            throw new \InvalidArgumentException('$directory must be a string specifying the path to a writable directory');
        }

        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function findByJob(JobInterface $job)
    {
        $path = $this->buildPath($job->getTicket());

        return file_exists($path) ? file_get_contents($path) : null;
    }

    /**Tests/
     * {@inheritdoc}
     */
    public function deleteByJob(JobInterface $job)
    {
        $path = $this->buildPath($job->getTicket());

        $filesystem = new Filesystem();
        if($filesystem->exists($path))
        {
            $filesystem->remove($path);
        }
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