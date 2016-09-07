<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger\Manager;

use Abc\Bundle\JobBundle\Logger\Manager\FileLogManager;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Test\MockHelper;
use Symfony\Component\Filesystem\Filesystem;
use phpmock\phpunit\PHPMock;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class FileLogManagerTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $json_decode;

    /**
     * @var
     */
    private $directory;

    /**
     * @var FileLogManager
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $directory         = dirname(__FILE__) . '/../../build/tests';
        $this->json_decode = $this->getFunctionMock(MockHelper::getNamespace(FileLogManager::class), 'json_decode');

        $this->setUpDirectory($directory);

        $this->directory = $directory;
        $this->subject   = new FileLogManager($this->directory);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructThrowsInvalidArgumentException()
    {
        new FileLogManager('path/to/nowhere');
    }

    public function testFindByJobWithNonExistingFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $this->assertEmpty($this->subject->findByJob($job));
    }

    public function testFindByJobWithEmptyFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        file_put_contents($this->buildFilename($job), '');
        $this->assertEmpty($this->subject->findByJob($job));
    }

    public function testFindByJobDecodesJson()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $line1 = ['message' => 'foo'];
        $line2 = ['message' => 'bar'];

        $content = json_encode($line1) . "/n" . json_encode($line2);

        file_put_contents($this->buildFilename($job), $content);

        $this->json_decode->expects($this->any())
            ->willReturnCallback(function ($data, $assoc) {
                return \json_decode($data, $assoc);
            });

        $this->assertEquals([$line1, $line2], $this->subject->findByJob($job));
    }

    public function testFindByJobHandlesNullDecodes()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        file_put_contents($this->buildFilename($job), '{asdasdasd');

        $this->assertEmpty($this->subject->findByJob($job));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFindByJobThrowsExceptionIfJsonDecodeFails()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $line1 = ['message' => 'foo'];
        $line2 = ['message' => 'bar'];

        $content = json_encode($line1) . "/n" . json_encode($line2);

        file_put_contents($this->buildFilename($job), $content);

        $this->json_decode->expects($this->any())
            ->willReturn(false);

        $this->subject->findByJob($job);
    }

    public function testDeleteLogWithExistingFile()
    {
        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        file_put_contents($this->buildFilename($job), 'foobar');

        $this->subject->deleteByJob($job);

        $this->assertFileNotExists($this->buildFilename($job));
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

    /**
     * @param JobInterface $job
     * @return string
     */
    private function buildFilename(JobInterface $job)
    {
        return $this->directory . DIRECTORY_SEPARATOR . $job->getTicket() . '.json';
    }

    private function setUpDirectory($path)
    {
        $filesystem = new Filesystem();

        if (is_dir($path)) {
            $filesystem->remove($path);
        }

        $filesystem->mkdir($path);
    }
}