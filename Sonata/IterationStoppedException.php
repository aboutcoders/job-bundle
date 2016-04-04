<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Sonata;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class IterationStoppedException extends \Exception
{
    const CODE = 0;

    public final function __construct()
    {
        return parent::__construct('Iteration stopped by process controll', self::CODE);
    }
} 