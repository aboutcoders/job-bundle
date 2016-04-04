<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Job;

use Abc\Bundle\JobBundle\Job\Context\Context;
use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\Invoker;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Tests\Fixtures\Job\TestJobAwareCallable;
use Metadata\MetadataFactoryInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /** @var JobTypeRegistry */
    private $registry;
    /** @var Invoker */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');
        $this->registry        = new JobTypeRegistry($this->metadataFactory);
        $this->subject         = new Invoker($this->registry);
    }

    /**
     * @dataProvider provideInvokeData
     */
    public function testInvoke($callable, $expectedResponse, $parameters = null, $parameterTypes = [], $contextParameters = [])
    {
        $context   = new Context($contextParameters);
        $serviceId = 'ServiceId';
        $type      = 'JobType';
        $job       = new Job($type, $parameters);

        $jobType   = new JobType($serviceId, $type, $callable);
        $jobType->setParameterTypes($parameterTypes);

        $this->registry->register($jobType);

        $this->assertEquals($expectedResponse, $this->subject->invoke($job, $context));
    }

    public function testInvokeHandlesJobAwareJobs()
    {
        $serviceId = 'serviceId';
        $type      = 'callable-type';
        $callable  = new TestJobAwareCallable();
        $jobType   = new JobType($serviceId, $type, array($callable, 'execute'));

        $job = new Job($type);

        $this->registry->register($jobType);

        $this->assertEquals('foobar', $this->subject->invoke($job, new Context()));
        $this->assertEquals($job, $callable->getJob());
    }

    /**
     * @return array
     */
    public static function provideInvokeData()
    {
        return [
            //[$callable, $expectedResponse, $parameters, $parameterTypes, $contextParameters],
            [function(){return 'foobar';}, 'foobar'],
            [function($argument) {return $argument; }, 'foobar', ['foobar'], ['string']],
            [function($contextParameter) {return $contextParameter; }, 'foobar', [], ['@contextParameter'], ['contextParameter' => 'foobar']],
            [function($parameter, $contextParameter) {return $parameter.$contextParameter; }, 'foobar', ['foo'], ['string', '@contextParameter'], ['contextParameter' => 'bar']],
            [function($parameter, $contextParameter) {return [$parameter,$contextParameter]; }, ['foo', null], ['foo'], ['string', '@contextParameter'], []],
            [function($parameter1, $contextParameter2, $parameter3) {return [$parameter1, $contextParameter2, $parameter3];}, ['foo', null, 'bar'], ['foo', 'bar'], ['string', '@contextParameter', 'string'], []],
        ];
    }
}