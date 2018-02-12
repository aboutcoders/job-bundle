<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Test;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MockHelper
{
    /**
     * @param $class
     * @return string
     */
    public static function getNamespace($class) {
        $pieces = explode("\\", $class);
        array_pop($pieces);
        return implode("\\", $pieces);
    }
}