<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Doctrine;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Doctrine\Job;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    /** @var SerializerInterface */
    private $serializer;

    public function setUp()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function testAllPropertiesExcluded()
    {
        /** @var JobTypeRegistry $registry */
        $registry = $this->getMockBuilder('Abc\Bundle\JobBundle\Job\JobTypeRegistry')->disableOriginalConstructor()->getMock();

        Job::setSerializer($this->serializer);
        Job::setRegistry($registry);

        $job = new Job();
        $job->setType('type');

        $data = $this->serializer->serialize($job, 'json');

        $dataArray = json_decode($data, true);

        $this->assertEquals(['type', 'status', 'processing_time', 'schedules'], array_keys($dataArray));
    }
}