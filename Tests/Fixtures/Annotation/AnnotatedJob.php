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
class AnnotatedJob
{
    /**
     * @ParamType("param", type="string")
     * @param $param
     */
    public function methodWithSingleParameters($param)
    {
    }

    /**
     * @ParamType("param1", type="string", options={})
     * @ParamType("param2", type="boolean", options={"groups"={"group1"}})
     * @param $param1
     * @param $param2
     */
    public function methodWithMultipleParameters($param1, $param2)
    {
    }

    /**
     * @ReturnType("string", options={})
     */
    public function methodWithResponse()
    {
    }
}