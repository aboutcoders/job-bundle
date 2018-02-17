<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Validator\Job;


use Abc\Bundle\JobBundle\Validator\Job\AbstractConstraintProvider;
use PHPUnit\Framework\TestCase;

class AbstractConstraintProviderTest extends TestCase
{
    public function testGetPriority()
    {
        $subject = $this->getMockForAbstractClass(AbstractConstraintProvider::class);
        $this->assertEquals(-1, $subject->getPriority());
    }
}
