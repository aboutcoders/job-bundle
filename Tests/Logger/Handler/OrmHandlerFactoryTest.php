<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger\Handler;

use Abc\Bundle\JobBundle\Logger\Handler\JobAwareOrmHandler;
use Abc\Bundle\JobBundle\Logger\Handler\OrmHandlerFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;
use Monolog\Logger;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class OrmHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var int
     */
    private $level;

    /**
     * @var boolean
     */
    private $bubble;

    /**
     * @var OrmHandlerFactory
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->level   = -100;
        $this->bubble  = false;
        $this->manager = $this->createMock(LogManagerInterface::class);
        $this->subject = new OrmHandlerFactory($this->level, $this->bubble, $this->manager);
    }

    /**
     * @param $level
     * @dataProvider provideLevels
     */
    public function testCreateHandler($level)
    {
        $job = new Job();
        $handler = $this->subject->createHandler($job, $level);

        $this->assertInstanceOf(JobAwareOrmHandler::class, $handler);
    }

    public static function provideLevels() {
        return [
            [Logger::CRITICAL],
            [null]
        ];
    }
}