<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

use JMS\Serializer\Annotation\Type;

/**
 * Response of a job if an exception was thrown during job execution.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ExceptionResponse
{
    /**
     * @var int
     * @Type("integer")
     */
    protected $code;

    /**
     * @var string
     * @Type("string")
     */
    protected $message;

    /**
     * @param string $message
     * @param int $code
     */
    function __construct($message, $code)
    {
        $this->code    = $code;
        $this->message = $message;
    }

    /**
     * @return int The exception code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string The exception message
     */
    public function getMessage()
    {
        return $this->message;
    }
}