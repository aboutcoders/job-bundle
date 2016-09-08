<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\Queue;

use Abc\Bundle\JobBundle\Job\Queue\QueueConfig;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class QueueConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testWithDefaultConstructor() {
        $subject = new QueueConfig();

        $this->assertEquals('default', $subject->getDefaultQueue());
        $this->assertEquals('default', $subject->getQueue('foobar'));
    }

    public function testWithConfig() {
        $subject = new QueueConfig([
            'queueA' => ['typeA'],
            'queueB' => ['typeB']
        ], 'custom');

        $this->assertEquals('custom', $subject->getDefaultQueue());
        $this->assertEquals('queueA', $subject->getQueue('typeA'));
        $this->assertEquals('queueB', $subject->getQueue('typeB'));
        $this->assertEquals('custom', $subject->getQueue('foobar'));
    }
}