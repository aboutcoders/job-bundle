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

use Abc\Bundle\JobBundle\Logger\Handler\OrmHandler;
use Abc\Bundle\JobBundle\Model\Log;
use Abc\Bundle\JobBundle\Model\LogInterface;
use Abc\Bundle\JobBundle\Model\LogManagerInterface;
use Monolog\Formatter\FormatterInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class OrmHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LogManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var OrmHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->manager = $this->createMock(LogManagerInterface::class);

        /** @var FormatterInterface|\PHPUnit_Framework_MockObject_MockObject $formatter */
        $formatter = $this->createMock(FormatterInterface::class);

        $formatter->expects($this->any())
            ->method('format')
            ->willReturn(['FormatedString']);

        $this->subject = $this->getMockBuilder(OrmHandler::class)
            ->setMethods(['isHandling', 'getFormatter', 'processRecord'])
            ->setConstructorArgs([$this->manager])->getMock();

        $this->subject->expects($this->once())
            ->method('getFormatter')
            ->willReturn($formatter);
    }

    public function testWrite()
    {
        $log = new Log();

        $record = [];
        $record['channel'] = 'Channel';
        $record['level'] = 'Level';
        $record['level_name'] = 'LevelName';
        $record['message'] = 'Message';
        $record['datetime'] = 'Datetime';
        $record['context'] = 'Context';
        $record['extra'] = 'Extra';

        $this->subject->expects($this->once())
            ->method('isHandling')
            ->willReturn(true);

        $this->subject->expects($this->once())
            ->method('processRecord')
            ->willReturnArgument(0);

        $this->manager->expects($this->once())
            ->method('create')
            ->willReturn($log);

        $this->manager->expects($this->once())
            ->method('save')
            ->with($this->callback(function(LogInterface $log)
            {
                return 'Channel' == $log->getChannel();

            }), true);

        $this->subject->handle($record);

        //$this->assertEquals($record[''], $log->get);
        $this->assertEquals($record['channel'], $log->getChannel());
        $this->assertEquals($record['level'], $log->getLevel());
        $this->assertEquals($record['level_name'], $log->getLevelName());
        $this->assertEquals($record['message'], $log->getMessage());
        $this->assertEquals($record['datetime'], $log->getDatetime());
        $this->assertEquals($record['context'], $log->getContext());
        $this->assertEquals($record['extra'], $log->getExtra());
    }
}