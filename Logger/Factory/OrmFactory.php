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

use Abc\Bundle\JobBundle\Job\Logger\AbstractFactory;
use Abc\Bundle\JobBundle\Logger\Handler\JobAwareOrmHandler;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class OrmFactory extends AbstractFactory
{
    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var LogManagerInterface
     */
    protected $manager;

    /**
     * @param JobTypeRegistry     $registry
     * @param LogManagerInterface $manager
     * @param FormatterInterface  $formatter
     * @param array               $processors
     * @param bool                $bubble
     */
    public function __construct(JobTypeRegistry $registry, LogManagerInterface $manager, FormatterInterface $formatter = null, array $processors = array(), $bubble = true)
    {
        parent::__construct($registry, $formatter, $processors, $bubble);

        $this->manager  = $manager;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function createHandler(JobInterface $job, $level = Logger::DEBUG, $bubble = true)
    {
        $handler = new JobAwareOrmHandler($this->manager, $level, $bubble);
        $handler->setJob($job);

        return $handler;
    }
}