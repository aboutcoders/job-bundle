<?php
/*
* This file is part of the job-bundle package.
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
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'mailer';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? MessageType::class : 'abc_job_message';
    }

    /**
     * {@inheritdoc}
     */
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
