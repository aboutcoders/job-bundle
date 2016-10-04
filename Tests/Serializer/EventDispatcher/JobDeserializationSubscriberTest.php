<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Serializer\EventDispatcher;

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Serializer\EventDispatcher\JobDeserializationSubscriber;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobDeserializationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobDeserializationSubscriber
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = new JobDeserializationSubscriber();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals([['event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize']], $this->subject->getSubscribedEvents());
    }

    /**
     * @param array $type
     * @param array $data
     * @dataProvider provideDataWhereTypesMustBeAdded
     */
    public function testOnPreDeserialize(array $type, array $data)
    {
        $event = $this->setupEvent($type, $data);

        $this->subject->onPreDeserialize($event);

        $data = $event->getData();

        $this->assertEquals(['abc.job.type' => $data['type']], array_pop($data['parameters']));
    }

    /**
     * @param array $type
     * @param array $data
     * @dataProvider provideDataWhereTypesMustNotBeAdded
     */
    public function testOnPreDeserializeOmitsAddingTypes(array $type, array $data)
    {
        $event = $this->setupEvent($type, $data);

        $this->subject->onPreDeserialize($event);

        $data = $event->getData();

        if (isset($data['parameters'])) {
            $this->assertArrayNotHasKey('abc.job.type', $data['parameters']);
        }
    }

    public static function provideDataWhereTypesMustBeAdded()
    {
        return [
            [['name' => Job::class], ['type' => 'JobType', 'parameters' => ['param1']]],
            [['name' => \Abc\Bundle\JobBundle\Doctrine\Job::class], ['type' => 'JobType', 'parameters' => ['param1']]],
            [['name' => \Abc\Bundle\JobBundle\Entity\Job::class], ['type' => 'JobType', 'parameters' => ['param1']]],
        ];
    }

    public static function provideDataWhereTypesMustNotBeAdded()
    {
        return [
            [['name' => JobInterface::class], ['type' => 'JobType', 'parameters' => ['param1']]],
            [['name' => Job::class], ['type' => 'JobType', 'parameters' => []]],
            [['name' => Job::class], ['type' => 'JobType', 'parameters' => null]],
        ];
    }

    /**
     * @param array $type
     * @param array $data
     * @return PreDeserializeEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function setupEvent(array $type, array $data)
    {
        /**
         * @var PreDeserializeEvent|\PHPUnit_Framework_MockObject_MockObject $event
         */
        $event = $this->getMockBuilder(PreDeserializeEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMock();

        $event->expects($this->any())
            ->method('getType')
            ->willReturn($type);

        $event->setData($data);

        return $event;
    }
}