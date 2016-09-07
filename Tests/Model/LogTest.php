<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Model;

use Abc\Bundle\JobBundle\Model\Log;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContext()
    {
        $subject = new Log();
        $this->assertTrue(is_array($subject->getContext()));
    }

    public function testGetExtra()
    {
        $subject = new Log();
        $this->assertTrue(is_array($subject->getExtra()));
    }
}