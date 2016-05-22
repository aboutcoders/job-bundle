<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job\ProcessControl;

use Abc\Bundle\JobBundle\Job\ProcessControl\Factory;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $job;

    /**
     * @var JobManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var Factory
     */
    private $subject;

    public function setUp()
    {
        $this->manager  = $this->getMock('Abc\Bundle\JobBundle\Model\JobManagerInterface');
        $this->job      = $this->getMock('Abc\Bundle\JobBundle\Model\JobInterface');
        $this->interval = 250;

        $this->subject = new Factory($this->manager, $this->interval);
    }

    public function testCreate()
    {
        $controller = $this->subject->create($this->job);
        $this->assertInstanceOf('Abc\Bundle\JobBundle\Job\ProcessControl\Controller', $controller);
    }
}