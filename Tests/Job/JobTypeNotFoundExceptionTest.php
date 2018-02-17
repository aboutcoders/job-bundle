<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job;

use Abc\Bundle\JobBundle\Job\JobTypeNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeNotFoundExceptionTest extends TestCase
{
    public function testGetType()
    {
        $subject = new JobTypeNotFoundException('foobar');
        $this->assertEquals('foobar', $subject->getType());
    }
}