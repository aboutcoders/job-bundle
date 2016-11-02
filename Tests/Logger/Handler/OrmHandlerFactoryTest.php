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
     * @var OrmHandlerFactory
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->manager = $this->createMock(LogManagerInterface::class);
        $this->subject = new OrmHandlerFactory($this->manager);
    }

    /**
     * @param int     $level
     * @param boolean $bubble
     * @dataProvider provideLevels
     */
    public function testCreateHandler($level, $bubble)
    {
        $job     = new Job();
        $handler = $this->subject->createHandler($job, $level, $bubble);

        $this->assertInstanceOf(JobAwareOrmHandler::class, $handler);
    }

    /**
     * @return array
     */
    public static function provideLevels()
    {
        return [
            [Logger::CRITICAL, true],
            [Logger::CRITICAL, false]
        ];
    }
}