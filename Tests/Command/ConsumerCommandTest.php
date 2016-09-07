<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Command;

use Abc\Bundle\JobBundle\Command\ConsumerCommand;
use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConsumerCommandTest extends KernelTestCase
{
    /**
     * @var ConsumerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->consumer = $this->getMock(ConsumerInterface::class);
    }

    /**
     * @dataProvider provideOptions
     */
    public function testExecute($queue, $options)
    {
        $expectedOptions = array_merge([
            'max-messages' => null,
            'stop-when-empty' => false,
            'max-runtime' => null
        ], $options);

        $tmp = [];
        foreach ($options as $key => $value) {
            $tmp['--' . $key] = $value;
        }
        $options = $tmp;

        self::bootKernel();

        static::$kernel->getContainer()->set('abc.job.consumer', $this->consumer);

        $application = new Application(self::$kernel);

        $application->add(new ConsumerCommand($this->consumer));

        $this->consumer->expects($this->once())
            ->method('consume')
            ->with($queue, $expectedOptions);

        $command = $application->find('abc:job:consume');

        $options['queue'] = $queue;
        $options['command'] = $command->getName();

        $commandTester = new CommandTester($command);
        $commandTester->execute($options);

        // the output of the command in the console
        // $output = $commandTester->getDisplay();
        // $this->assertContains('Username: Wouter', $output);

    }

    public static function provideOptions() {
        return [
            ['foobar', []],
            ['foobar', ['max-messages' => 1]]
        ];
    }
}