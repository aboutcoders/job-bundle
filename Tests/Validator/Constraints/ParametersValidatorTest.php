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
use Abc\Bundle\JobBundle\Validator\Constraints\Parameters as ParametersConstraint;
use Abc\Bundle\JobBundle\Validator\Constraints\ParametersValidator;
use Abc\Bundle\JobBundle\Validator\Job\ConstraintProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ParametersValidatorTest extends TestCase
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
     * @var ParametersValidator
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->registry = $this->getMockBuilder(JobTypeRegistry::class)->disableOriginalConstructor()->getMock();
        $this->subject  = new ParametersValidator($this->registry);
        $this->context  = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $this->subject->initialize($this->context);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testValidateThrowsExceptionIfTypeNotSet()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate('foobar', new ParametersConstraint());
    }

    public function testValidateWithTypeWithoutConstraints()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate('foobar', new ParametersConstraint(['type' => 'foobar']));
    }

    public function testValidateWithInvalidValue()
    {
        $value      = 'foobar';
        $builder    = $this->createMock(ConstraintViolationBuilderInterface::class);
        $provider   = $this->createMock(ConstraintProviderInterface::class);
        $constraint = $this->getMockForAbstractClass(Constraint::class);

        $this->registry->expects($this->once())
            ->method('getTypeChoices')
            ->willReturn(['foobar']);

        $provider->expects($this->any())
            ->method('getConstraints')
            ->with('foobar')
            ->willReturn([$constraint]);

        $this->subject->register($provider);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('The value must be an array.')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('addViolation');

        $this->subject->validate($value, new ParametersConstraint(['type' => 'foobar']));
    }

    /**
     * @dataProvider provideValuesWithConstraints
     * @param array $parameters
     * @param array $constraints
     */
    public function testValidateWithValue(array $parameters, array $constraints)
    {
        $validator           = $this->createMock(ValidatorInterface::class);
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $provider            = $this->createMock(ConstraintProviderInterface::class);

        $this->subject->register($provider);

        $this->registry->expects($this->once())
            ->method('getTypeChoices')
            ->willReturn(['foobar']);

        $provider->expects($this->any())
            ->method('getConstraints')
            ->with('foobar')
            ->willReturn($constraints);

        $this->context->expects($this->once())
            ->method('getValidator')
            ->willReturn($validator);

        $validator->expects($this->once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidator);

        $contextualValidator->expects($this->exactly(count($constraints)))
            ->method('atPath')
            ->willReturn($contextualValidator);

        $contextualValidator->expects($this->exactly(count($constraints)))
            ->method('validate');

        $this->subject->validate($parameters, new ParametersConstraint(['type' => 'foobar']));
    }

    public function provideValuesWithConstraints()
    {
        return [
            [
                ['value1', 'value2'],
                [
                    $this->getMockForAbstractClass(Constraint::class),
                    $this->getMockForAbstractClass(Constraint::class)
                ]
            ],
            [
                ['value1'],
                [
                    $this->getMockForAbstractClass(Constraint::class),
                    $this->getMockForAbstractClass(Constraint::class)
                ]
            ],
            [
                ['value1', 'value2'],
                [
                    $this->getMockForAbstractClass(Constraint::class),
                ]
            ],
        ];
    }
}
