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
class ParameterConstraintViolation
{
    /**
     * @Type("string")
     * @var string
     */
    protected $name;

    /**
     * @Type("string")
     * @var string
     */
    protected $message;

    /**
     * @param string $name
     * @param string $message
     */
    public function __construct($name, $message)
    {
        $this->name    = $name;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}