<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Validator\Constraints;


use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Validator\Constraints\JobType;
use Abc\Bundle\JobBundle\Validator\Constraints\JobTypeValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class JobTypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobTypeRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var ExecutionContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var JobTypeValidator
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->registry = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->subject  = new JobTypeValidator($this->registry);
        $this->context  = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $this->subject->initialize($this->context);
    }

    public function testValidateWithNull()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate(null, new JobType());
    }

    public function testWithTypeNotRegistered()
    {
        $value = 'foobar';
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->registry->expects($this->once())
            ->method('has')
            ->with($value)
            ->willReturn(false);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('setParameter')
            ->with('{{string}}', $value)
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('addViolation');

        $this->subject->validate($value, new JobType());
    }

    public function testValidateWithTypeRegistered()
    {
        $value = 'foobar';

        $this->registry->expects($this->once())
            ->method('has')
            ->with($value)
            ->willReturn(true);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate($value, new JobType());
    }
}
