<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Logger\Handler;

use Abc\Bundle\JobBundle\Model\LogInterface;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class OrmHandler extends AbstractProcessingHandler
{
    /**
     * @var LogManagerInterface
     */
    protected $manager;

    /**
     * @param LogManagerInterface $manager
     * @param bool|int            $level defaults to Monolog\Logger\Logger::DEBUG
     * @param bool                $bubble defaults to true
     */
    public function __construct(LogManagerInterface $manager, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $log = $this->manager->create();

        $this->populateLog($log, $record);

        $this->manager->save($log);
    }

    /**
     * @param LogInterface $log
     * @param              $record
     */
    protected function populateLog(LogInterface $log, $record)
    {
        $log->setChannel($record['channel']);
        $log->setLevel($record['level']);
        $log->setLevelName($record['level_name']);
        $log->setMessage($record['message']);
        $log->setDatetime($record['datetime']);
        $log->setContext($record['context']);
        $log->setExtra($record['extra']);
    }
}