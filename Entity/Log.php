<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Entity;

use Abc\Bundle\JobBundle\Model\Log as BaseLog;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Log extends BaseLog
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $jobTicket;

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
     * @param string $jobTicket
     */
    public function setJobTicket($jobTicket)
    {
        $this->jobTicket = $jobTicket;
    }
}