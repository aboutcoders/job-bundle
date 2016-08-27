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

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class LogManager implements LogManagerInterface
{
    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $class = $this->getClass();

        /**
         * @var LogInterface $log
         */
        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function findByJob(BaseJobInterface $job)
    {
        $records = array();
        foreach ($this->findBy(['jobTicket' => $job->getTicket()], ['datetime' => 'ASC']) as $log) {
            /**
             * @var LogInterface $log
             */
            $records[] = $log->toRecord();
        }

        return $records;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByJob(BaseJobInterface $job)
    {
        return $this->deleteLogs($this->findBy(['jobTicket' => $job->getTicket()]));
    }

    /**
     * @param LogInterface[] $logs
     * @return int the number of delete entries
     */
    protected function deleteLogs($logs)
    {
        $i = 0;
        foreach ($logs as $log) {
            $this->delete($log);
            $i++;
        }

        return $i;
    }
}