<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Exception;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TicketNotFoundException extends \Exception
{
    const CODE = 404;

    /** @var string */
    private $ticket;

    /**
     * @param string $ticket
     */
    public function __construct($ticket)
    {
        parent::__construct(sprintf('Ticket "%s" not found', $ticket), self::CODE);

        $this->ticket = $ticket;
    }

    /**
     * @return string
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}