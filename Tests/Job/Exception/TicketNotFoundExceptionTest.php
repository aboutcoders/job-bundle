<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Exception;

use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TicketNotFoundExceptionTest extends TestCase
{
    public function testGetTicket()
    {
        $exception = new TicketNotFoundException('ticket');
        $this->assertEquals('ticket', $exception->getTicket());
    }
}