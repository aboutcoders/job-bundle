<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Parameters extends Constraint
{
    public $type;
    public $message = 'The value must be an array.';
}