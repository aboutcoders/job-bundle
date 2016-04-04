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

use JMS\Serializer\Annotation\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Message
{
    /**
     * @var string
     * @Type("string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $to;

    /**
     * @var string
     * @Type("string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $from;

    /**
     * @var string
     * @Type("string")
     */
    protected $subject;

    /**
     * @var string
     * @Type("string")
     */
    protected $message;

    /**
     * @param string|null $to
     * @param string|null $from
     * @param string|null $subject
     * @param string|null $message
     */
    function __construct($to = null, $from = null, $subject = null, $message = null)
    {
        $this->to      = $to;
        $this->from    = $from;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}