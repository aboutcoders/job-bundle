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

use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Job\JobInterface as BaseJobInterface;

/**
 * Defines API of a (persistable) job entity.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface JobInterface extends BaseJobInterface
{
    /**
     * @param string $ticket
     * @return void
     */
    public function setTicket($ticket);

    /**
     * @param string $type
     * @return void
     */
    public function setType($type);

    /**
     * @param Status $status
     * @return void
     */
    public function setStatus(Status $status);

    /**
     * Sets the response of the root job
     *
     * @param mixed|null $response The serialized response
     */
    public function setResponse($response = null);

    /**
     * @param double $processingTime The processing time in microseconds
     * @return void
     */
    public function setProcessingTime($processingTime);

    /**
     * @param \DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @param \DateTime $terminatedAt
     * @return void
     */
    public function setTerminatedAt(\DateTime $terminatedAt);
}