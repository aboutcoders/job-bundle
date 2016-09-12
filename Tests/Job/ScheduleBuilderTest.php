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

use Abc\Bundle\JobBundle\Job\ScheduleBuilder;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $schedule = ScheduleBuilder::create('cron', '* * * * *');

        $this->assertEquals('cron', $schedule->getType());
        $this->assertEquals('* * * * *', $schedule->getExpression());
    }
}