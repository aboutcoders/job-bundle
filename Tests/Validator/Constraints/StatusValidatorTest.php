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


use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Validator\Constraints\Status as StatusConstraint;
use Abc\Bundle\JobBundle\Validator\Constraints\StatusValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class StatusValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExecutionContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var StatusValidator
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = new StatusValidator();
        $this->context = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $this->subject->initialize($this->context);
    }

    public function testValidateWithNull()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate(null, new StatusConstraint());
    }

    public function testWithInvalidType()
    {
        $value   = 'foobar';
        $builder = $this->getMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('setParameter')
            ->with('{{string}}', $value)
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('addViolation');

        $this->subject->validate($value, new StatusConstraint());
    }

    public function testValidateWithValidStatus()
    {
        foreach (Status::toArray() as $value) {
            $this->context->expects($this->never())
                ->method('buildViolation');

            $this->subject->validate($value, new StatusConstraint());
        }
    }
}
