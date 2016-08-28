<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Logger\Factory;

use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Queue\QueueConfig;
use Abc\Bundle\JobBundle\Logger\Factory\FileLoggerFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Metadata\MetadataFactoryInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileLoggerFactory
     */
    private $subject;


    public function setUp()
    {
        $this->directory       = dirname(__FILE__) . '/../../../build/tests';
        $this->metadataFactory = $this->getMock(MetadataFactoryInterface::class);

        $this->registry = new JobTypeRegistry($this->metadataFactory, new QueueConfig());
        $this->logger   = $this->getMock(LoggerInterface::class);

        $this->setUpTestDir($this->directory);

        $this->subject = new FileLoggerFactory($this->registry, $this->directory);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidConstructorArgs
     * @param       $registry
     * @param       $path
     * @param array $processors
     */
    public function testConstructThrowsInvalidArgumentException($registry, $path, $processors = array())
    {
        new FileLoggerFactory($registry, $path, $processors);
    }

    public function testCreate()
    {
        $this->registry->register(new JobType('service-id', 'job-type', function(){}, Logger::DEBUG));

        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $logger = $this->subject->create($job);

        $this->assertInstanceOf('Monolog\Logger', $logger);
        $this->assertEquals('', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());

        $handlers = $logger->getHandlers();

        /**
         * @var \Monolog\Handler\StreamHandler $handler
         */
        $handler = $handlers[0];

        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertEquals(Logger::DEBUG, $handler->getLevel());

        $logger->info('foobar');

        $this->assertFileExists($this->directory . DIRECTORY_SEPARATOR . $job->getTicket() . '.log');
    }

    public function testCreateWithProcessor()
    {
        $processor = function () {
        };

        $this->registry->register(new JobType('service-id', 'job-type', function () {
        }, Logger::DEBUG));

        $this->subject->addProcessor($processor);

        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $logger   = $this->subject->create($job);
        $handlers = $logger->getHandlers();

        /**
         * @var \Monolog\Handler\StreamHandler $handler
         */
        $handler = $handlers[0];

        $this->assertSame($handler->popProcessor(), $processor);
    }

    public function testAddProcessorAcceptsCallable()
    {
        $this->subject->addProcessor(function() {});
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddProcessorThrowsInvalidArgumentException()
    {
        $this->subject->addProcessor('foo');
    }

    public function getInvalidConstructorArgs()
    {
        return [
            [new JobTypeRegistry($this->getMock(MetadataFactoryInterface::class), new QueueConfig()), 'path/to/nowhere'],
            [new JobTypeRegistry($this->getMock(MetadataFactoryInterface::class), new QueueConfig()), sys_get_temp_dir(), ['foo']]
        ];
    }

    private function setUpTestDir($path)
    {
        $filesystem = new Filesystem();

        if (is_dir($path)) {
            $filesystem->remove($path);
        }

        $filesystem->mkdir($path);
    }
}