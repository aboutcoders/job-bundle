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

use Abc\Bundle\SchedulerBundle\Model\Schedule as BaseSchedule;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 *
 * @ExclusionPolicy("all")
 */
class Schedule extends BaseSchedule implements ScheduleInterface
{
    /**
     * @var string|null
     * @Type("string")
     * @Expose
     */
    protected $type;

    /**
     * @var string|null
     * @Type("string")
     * @Expose
     */
    protected $expression;

    /**
     * @var bool
     * @Type("boolean")
     * @Expose
     */
    protected $isActive;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @param string|null $type
     * @param string|null   $expression
     */
    public function __construct($type = null, $expression = null)
    {
        $this->type = (null == $type) ? 'cron' : $type;
        $this->isActive = true;
        $this->expression = $expression;
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * {@inheritDoc}
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * {@inheritDoc}
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * {@inheritDoc}
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        parent::__clone();

        $this->createdAt = null;
        $this->updatedAt = null;
    }
} 