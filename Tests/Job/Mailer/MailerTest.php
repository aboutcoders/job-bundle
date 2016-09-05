<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Mailer;

use Abc\Bundle\JobBundle\Job\Mailer\Mailer;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Test\JobTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MailerTest extends JobTestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
    }

    public function testJobIsRegistered()
    {
        $this->assertJobIsRegistered('abc.mailer');
    }

    public function testClass()
    {
        $this->assertJobClass('abc.mailer', Mailer::class);
    }

    public function testParameters()
    {

        $message = new Message('mail@domain.tld', 'to@domain.td', 'Subject', 'MessageBody');

        $this->assertJobInvokedWithParams('abc.mailer', [$message]);
    }
}