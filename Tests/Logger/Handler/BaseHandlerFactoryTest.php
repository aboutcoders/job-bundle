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

use Abc\Bundle\JobBundle\Logger\Handler\BaseHandlerFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class BaseHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseHandlerFactory
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = $this->getMockForAbstractClass(BaseHandlerFactory::class);
    }

    public function testInitHandler() {

        $handler = $this->createMock(HandlerInterface::class);
        $formatter = $this->createMock(FormatterInterface::class);
        $processors = ['foobar'];

        $this->subject->setFormatter($formatter);
        $this->subject->setProcessors($processors);

        $handler->expects($this->once())
            ->method('setFormatter')
            ->with($formatter);

        $handler->expects($this->once())
            ->method('pushProcessor')
            ->with('foobar');

        $this->invokeMethod($this->subject, 'initHandler', [$handler]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     * @return mixed
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}