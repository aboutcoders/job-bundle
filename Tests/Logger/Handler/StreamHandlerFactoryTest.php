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

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StreamHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var StreamHandlerFactory
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->root    = vfsStream::setup();
        $this->subject = new StreamHandlerFactory($this->root->url());
    }

    /**
     * @param int     $level
     * @param boolean $bubble
     * @dataProvider provideLevels
     */
    public function testCreateHandler($level, $bubble)
    {
        $job = new Job();
        $job->setTicket('JobTicket');

        $handler = $this->subject->createHandler($job, $level, $bubble);
        $this->assertInstanceOf(StreamHandler::class, $handler);
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