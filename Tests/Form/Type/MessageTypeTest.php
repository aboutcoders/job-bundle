<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Form\Type;

use Abc\Bundle\JobBundle\Form\Type\MessageType;
use Abc\Bundle\JobBundle\Job\Mailer\Message;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MessageTypeTest extends TypeTestCase
{

    public function getType()
    {
        return 'mailer';
    }

    public function getFormType()
    {
        return new MessageType();
    }

    public function provideTestData()
    {
        return [
            [
                [
                    'to' => "to@domain.tld",
                    'from' => "from@domain.tld",
                    'message' => "MESSAGE",
                    'subject' => "SUBJECT",
                ],
                [new Message('to@domain.tld', 'from@domain.tld', 'SUBJECT', 'MESSAGE')]
            ]
        ];
    }
}
