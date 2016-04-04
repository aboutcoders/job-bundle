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

use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface as BaseScheduleInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ScheduleInterface extends BaseScheduleInterface
{
    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive);

    /**
     * @return boolean
     */
    public function getIsActive();

    /**
     * @param JobInterface $job
     */
    public function setJob(JobInterface $job);

    /**
     * @return JobInterface
     */
    public function getJob();

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @return \DateTime
     */
    public function getCreatedAt();
} 