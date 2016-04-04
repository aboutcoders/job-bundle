<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Context\Exception;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ParameterNotFoundException extends  \InvalidArgumentException
{

    /** @var string */
    protected $name;

    /**
     * @param string $name The parameter name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf('A parameter with the name "%s" does not exist', $name));

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}