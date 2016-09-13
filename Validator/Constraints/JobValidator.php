<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Validator\Constraints;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Abc\Bundle\JobBundle\Validator\Constraints as AssertJob;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if(!$value instanceof JobInterface) {
            throw new \InvalidArgumentException('The value must be an instance of '.JobInterface::class);
        }

        if(null == $value->getType()) {
            return;
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->atPath('parameters')
            ->validate($value->getParameters(), new AssertJob\Parameters(['type' => $value->getType()]));

        return;
    }
}