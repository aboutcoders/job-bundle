<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger;

use Abc\Bundle\JobBundle\Logger\StreamLogManager;
use Abc\Bundle\JobBundle\Model\Job;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StreamLogManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    private $directory;

    /**
     * @var StreamLogManager
     */
    private $subject;

    public function setUp()
    {
        $directory = dirname(__FILE__) . '/../../build/tests';

        $this->setUpDirectory($directory);

        $this->directory = $directory;
        $this->subject   = new StreamLogManager($this->directory);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructThrowsInvalidArgumentException()
    {
        new StreamLogManager('path/to/nowhere');
    }

    public function testFetchLogWithExistingFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $this->assertNull($this->subject->findByJob($job));
    }

    public function testFetchLogWithNonExistingFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        file_put_contents($this->directory . DIRECTORY_SEPARATOR . $job->getTicket() . '.log', 'foobar');

        $this->assertEquals('foobar', $this->subject->findByJob($job));
    }

    public function testDeleteLogWithExistingFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        file_put_contents($this->directory . DIRECTORY_SEPARATOR . $job->getTicket() . '.log', 'foobar');

        $this->subject->deleteByJob($job);

        $this->assertFileNotExists($this->directory . DIRECTORY_SEPARATOR . $job->getTicket() . '.log');
    }

    public function testDeleteLogWithNonExistingFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $this->subject->deleteByJob($job);
    }

    public function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->directory);
    }

    private function setUpDirectory($path)
    {
        $filesystem = new Filesystem();

        if(is_dir($path))
        {
            $filesystem->remove($path);
        }

        $filesystem->mkdir($path);
    }
}