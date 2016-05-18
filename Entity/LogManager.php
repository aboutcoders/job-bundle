<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Entity;

use Abc\Bundle\JobBundle\Doctrine\LogManager as BaseLogManager;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Model\LogInterface;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogManager extends BaseLogManager
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager       $em
     * @param string              $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $class);

        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function save(LogInterface $log, $andFlush = true)
    {
        if(!$log instanceof $this->class)
        {
            throw new InvalidArgumentException('1st argument must be an instanceof '.$this->getClass());
        }

        $extra = $log->getExtra();
        if(is_array($extra) && isset($extra['job_ticket']))
        {
            /** @var \Abc\Bundle\JobBundle\Entity\Log $log */
            $log->setJobTicket($extra['job_ticket']);

            unset($extra['job_ticket']);

            $log->setExtra($extra);
        }

        parent::save($log, $andFlush);
    }

    /**
     * {@inheritdoc}
     */
    public function findByJob(JobInterface $job)
    {
        return $this->formatLogs($this->findBy(['jobTicket' => $job->getTicket()]));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByJob(JobInterface $job)
    {
        return $this->deleteLogs($this->findBy(['jobTicket' => $job->getTicket()]));
    }
}