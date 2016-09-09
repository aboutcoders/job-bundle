<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Api;

use JMS\Serializer\Annotation\Type;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ErrorResponse
{
    /**
     * @Type("string")
     * @var string
     */
    protected $message;

    /**
     * @Type("string")
     * @var string
     */
    protected $description;

    /**
     * ype("array<string>")
     * @var array
     */
    protected $errors;

    /**
     * @param string $message
     * @param string $description
     */
    public function __construct($message, $description)
    {
        $this->message     = $message;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return void
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
}