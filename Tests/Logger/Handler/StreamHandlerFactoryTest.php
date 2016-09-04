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

use Abc\Bundle\JobBundle\Logger\Handler\StreamHandlerFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;


class StreamHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $level;

    /**
     * @var bool
     */
    private $bubble;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var StreamHandlerFactory
     */
    private $subject;

    public function setUp()
    {
        $this->level   = 100;
        $this->bubble  = false;
        $this->root    = vfsStream::setup();
        $this->subject = new StreamHandlerFactory($this->level, $this->bubble, $this->root->url());
    }

    /**
     * @param $level
     * @dataProvider provideLevels
     */
    public function testCreateHandler($level)
    {
        $job = new Job();
        $job->setTicket('JobTicket');

        $handler = $this->subject->createHandler($job);
        $this->assertInstanceOf(StreamHandler::class, $handler);
    }

    public static function provideLevels() {
        return [
            [Logger::CRITICAL],
            [null]
        ];
    }
}