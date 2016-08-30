<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Fixtures\Annotation;

use Abc\Bundle\JobBundle\Annotation\ParamType;
use Abc\Bundle\JobBundle\Annotation\ReturnType;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AnnotatedJob {


    /**
     * @ParamType("string")
     */
    public function methodWithSingleParameters($string)
    {
    }

    /**
     * @ParamType({"string","boolean"})
     */
    public function methodWithMultipleParameters($string, $boolean)
    {
    }

    /**
     * @ReturnType("string")
     */
    public function methodWithResponse()
    {
    }
}