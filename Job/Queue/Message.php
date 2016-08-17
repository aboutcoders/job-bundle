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
 * Message to be exchanged between a queue engine and the job manager
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Message {

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $ticket;

    /**
     * @param string $type
     * @param string $ticket
     */
    function __construct($type, $ticket)
    {
        $this->type     = $type;
        $this->ticket   = $ticket;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}