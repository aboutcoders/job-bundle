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

use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\Schedule as BaseSchedule;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Schedule extends BaseSchedule
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $jobTicket;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJobTicket()
    {
        return $this->jobTicket;
    }

    /**
     * @return JobInterface|null
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param JobInterface|null $job
     */
    public function setJob(JobInterface $job = null)
    {
        $this->job = $job;
    }

    /**
     * Override clone in order to avoid duplicating entries in Doctrine
     */
    public function __clone()
    {
        parent::__clone();

        $this->id = null;
    }
}