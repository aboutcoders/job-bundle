<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
final class ParamType
{
    /**
     * @Required
     * @var string
     */
    public $name;

    /**
     * @Required
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $options = array();
}
