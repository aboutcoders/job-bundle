<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Validator\Constraints;

use Abc\Bundle\JobBundle\Validator\Constraints\Job;
use Symfony\Component\Validator\Constraint;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTargets()
    {
        $subject = new Job();
        $this->assertContains(Constraint::CLASS_CONSTRAINT, $subject->getTargets());
        $this->assertContains(Constraint::PROPERTY_CONSTRAINT, $subject->getTargets());
    }
}
