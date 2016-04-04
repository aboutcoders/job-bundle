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

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Annotation\JobResponse;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TestJob {


    /**
     * @JobParameters("string")
     */
    public function methodWithSingleParameters($string)
    {
    }

    /**
     * @JobParameters({"string","boolean"})
     */
    public function methodWithMultipleParameters($string, $boolean)
    {
    }

    /**
     * @JobResponse("string")
     */
    public function methodWithResponse()
    {
    }
} 