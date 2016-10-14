<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Queue;

/**
 * The message to be published/consumed with the queue backend
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface MessageInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string|null
     */
    public function getTicket();

    /**
     * @return array|null
     */
    public function getParameters();
}