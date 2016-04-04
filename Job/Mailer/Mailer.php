<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Mailer;

use Abc\Bundle\JobBundle\Job\JobAwareInterface;
use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Mailer implements JobAwareInterface
{
    /**
     * @var JobInterface
     */
    private $job;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * Sends a mail.
     *
     * @param Message         $message
     * @param LoggerInterface $logger
     * @throws \Exception Rethrows exceptions thrown by mailer
     * @JobParameters({"Abc\Bundle\JobBundle\Job\Mailer\Message", "@logger"})
     */
    public function send(Message $message, LoggerInterface $logger)
    {
        $logger->debug('Send mail {message}', array('message' => $message));

        $mail = $this->mailer->createMessage()
            ->setSubject($message->getSubject())
            ->setFrom($message->getFrom())
            ->setTo($message->getTo());

        $mail->addPart($message->getMessage(), 'text/plain');

        try
        {
            $this->mailer->send($mail);

            $this->mailer->getTransport()->stop();
        }
        catch(\Exception $e)
        {
            $this->mailer->getTransport()->stop();

            throw $e;
        }
    }
}