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
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface HandlerFactoryInterface
{
    /**
     * @param JobInterface $job
     * @param int|null     $level The minimum logging level at which this handler will be triggered
     * @return HandlerInterface
     */
    public function createHandler(JobInterface $job, $level = null);

    /**
     * @param FormatterInterface $formatter
     * @return void
     */
    public function setFormatter(FormatterInterface $formatter = null);

    /**
     * @param array $processors
     * @return void
     */
    public function setProcessors(array $processors);
}