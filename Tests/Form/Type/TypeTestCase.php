<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Form\Type;

use Abc\Bundle\JobBundle\Form\Type\JobType as FormJobType;
use Abc\Bundle\JobBundle\Job\JobType;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Entity\Job;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Tests\Extension\Validator\Type\TypeTestCase as BaseTypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class TypeTestCase extends BaseTypeTestCase
{
    /** @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $registry;

    /**
     * Returns the job type
     *
     * @return string
     */
    public abstract function getType();

    /**
     * Returns the form class configured for the job
     *
     * @return string|null
     */
    public abstract function getFormClass();

    /**
     * Provides the test data.
     *
     * Each data set must be an array containing two elements:
     *  1. The valid form data
     *  2. The expected parameters passed to the job
     *
     * Example:
     * [
     *      'to' => "to@domain.tld",
     *      'message' => "Hello World"
     * ],
     * [new MyMessage('to@domain.tld', 'HelloWorld')]
     *
     * @return array
     * @see Abc\Bundle\JobBundle\Tests\Form\Type\MessageTypeTestCase as an example
     */
    public abstract function provideTestData();


    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Abc\Bundle\JobBundle\Job\JobTypeRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var SerializerInterface $serializer */
        $serializer = $this->getMock('JMS\Serializer\SerializerInterface');

        Job::setSerializer($serializer);
        Job::setRegistry($this->registry);

        $this->registry
            ->method('has')
            ->willReturn(true);

        parent::setUp();

        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
    }

    /**
     * @param array $formData
     * @param array $expectedParameters
     * @dataProvider provideTestData
     */
    public function testSubmitValidData($formData, $expectedParameters)
    {
        // prepare formData
        $formData = [
            'type' => $this->getType(),
            'parameters' => $formData
        ];

        // prepare other objects
        $job = new Job();
        $job->setType($this->getType());

        $expectedJob = clone $job;
        $expectedJob->setParameters($expectedParameters);

        $jobType = new JobType('serviceId', $this->getType(), function (){});
        $jobType->setFormClass($this->getFormClass());

        $this->registry
            ->method('get')
            ->willReturn($jobType);

        // test
        $form = $this->factory->create(FormJobType::class, $job);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedJob, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach(array_keys($formData) as $key)
        {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $type = new FormJobType($this->registry);

        return array_merge(
            parent::getExtensions(),
            array(
                new PreloadedExtension(array($type), array()),
            )
        );
    }
}