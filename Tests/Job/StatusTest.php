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

use Abc\Bundle\JobBundle\Job\Status;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{

    public function testGetName()
    {
        $this->assertEquals('CANCELLED', Status::CANCELLED()->getName());
    }

    public function test__toString()
    {
        $this->assertEquals('CANCELLED',  (string) Status::CANCELLED());
    }


    public function testUnterminatedStatusValues()
    {
        $unterminatedValues = Status::getTerminatedStatusValues();
        foreach(array(Status::CANCELLED(), Status::PROCESSED(), Status::ERROR()) as $status)
        {
            $this->assertContains($status->getValue(), $unterminatedValues);
        }
    }
}