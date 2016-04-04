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

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeNotFoundException extends \Exception
{
    const CODE = 404;

    /** @var string */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct(sprintf('Definition "%s" not found', $type), self::CODE);

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
} 