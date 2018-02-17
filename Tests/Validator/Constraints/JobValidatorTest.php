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

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Validator\Constraints\Job as JobConstraint;
use Abc\Bundle\JobBundle\Validator\Constraints\JobValidator;
use Abc\Bundle\JobBundle\Validator\Constraints\Parameters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobValidatorTest extends TestCase
{

    /**
     * @var ExecutionContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var JobValidator
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = new JobValidator();
        $this->context = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $this->subject->initialize($this->context);
    }

    public function testValidateWithNull()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate(null, new JobConstraint());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithWrongInstance()
    {
        $this->subject->validate(new \stdClass(), new JobConstraint());
    }

    public function testValidateWithTypeIsNull()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate(new Job(), new JobConstraint());
    }

    public function testValidateWithType()
    {
        $job = new Job();
        $job->setType('foobar');
        $job->setParameters(['JobParameters']);

        $validator           = $this->createMock(ValidatorInterface::class);
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $this->context->expects($this->once())
            ->method('getValidator')
            ->willReturn($validator);

        $validator->expects($this->once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidator);

        $contextualValidator->expects($this->once())
            ->method('atPath')
            ->with('parameters')
            ->willReturn($contextualValidator);

        $contextualValidator->expects($this->once())
            ->method('validate')
            ->with(['JobParameters'], new Parameters(['type' => $job->getType()]));

        $this->subject->validate($job, new JobConstraint());
    }
}