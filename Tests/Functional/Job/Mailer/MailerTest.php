<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Job\Mailer;

use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Test\JobTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MailerTest extends JobTestCase
{
    public function testJobIsRegistered()
    {
        $this->assertJobIsRegistered('abc.mailer', 'abc.job.mailer', 'send');
    }

    public function testParameters()
    {
        $message = new Message('mail@domain.tld', 'to@domain.td', 'Subject', 'MessageBody');

        $this->assertInvokesJob('abc.mailer', [$message]);
    }
}