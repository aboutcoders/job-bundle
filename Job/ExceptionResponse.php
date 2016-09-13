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
     * @Type("integer")
     * @var int
     */
    protected $code;

    /**
     * @Type("string")
     * @var string
     */
    protected $message;

    /**
     * @param \Exception $e
     */
    function __construct(\Exception $e)
    {
        $this->code    = $e->getCode();
        $this->message = $e->getMessage();
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