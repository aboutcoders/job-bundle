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
use Abc\Bundle\JobBundle\Logger\Factory\StreamFactory;
use Abc\Bundle\JobBundle\Model\Job;
use Metadata\MetadataFactoryInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StreamFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @var StreamFactory
     */
    private $subject;


    public function setUp()
    {
        $this->directory = dirname(__FILE__) .'/../../../build/tests';
        $this->metadataFactory = $this->getMock(MetadataFactoryInterface::class);

        $this->registry = new JobTypeRegistry($this->metadataFactory);
        $this->logger = $this->getMock(LoggerInterface::class);

        $this->setUpTestDir($this->directory);

        $this->subject = new StreamFactory($this->registry, $this->directory);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidConstructorArgs
     */
    public function testConstructThrowsInvalidArgumentException($registry, $path, $formatter = null, $processors = array())
    {
        new StreamFactory($registry, $path, $formatter, $processors);
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
        /** @var \Monolog\Handler\StreamHandler $handler */
        $handler = $handlers[0];

        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertEquals(Logger::DEBUG, $handler->getLevel());

        $logger->info('foobar');

        $this->assertFileExists($this->directory . DIRECTORY_SEPARATOR . $job->getTicket() . '.log');
    }

    public function testCreateWithFormatterAndProcessor()
    {
        /** @var FormatterInterface|\PHPUnit_Framework_MockObject_MockObject $formatter */
        $formatter = $this->getMock(FormatterInterface::class);
        $processor = function() {};

        $this->registry->register(new JobType('service-id', 'job-type', function(){}, Logger::DEBUG));

        $this->subject->addProcessor($processor);
        $this->subject->setFormatter($formatter);

        $job = new Job();
        $job->setTicket('job-ticket');
        $job->setType('job-type');

        $logger = $this->subject->create($job);
        $handlers = $logger->getHandlers();
        /** @var \Monolog\Handler\StreamHandler $handler */
        $handler = $handlers[0];

        $this->assertSame($formatter, $handler->getFormatter());
        $this->assertSame($handler->popProcessor(), $processor);
    }

    public function testSetFormatter()
    {
        $this->subject->setFormatter($this->getMock('Monolog\Formatter\FormatterInterface'));
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
        return array(
            array(new JobTypeRegistry($this->getMock('Metadata\MetadataFactoryInterface')), 'path/to/nowhere'),
            array(new JobTypeRegistry($this->getMock('Metadata\MetadataFactoryInterface')), sys_get_temp_dir(), null, array('foo'))
        );
    }

    private function setUpTestDir($path)
    {
        $filesystem = new Filesystem();

        if(is_dir($path))
        {
            $filesystem->remove($path);
        }

        $filesystem->mkdir($path);
    }
}