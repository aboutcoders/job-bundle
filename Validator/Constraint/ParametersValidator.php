<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ParameterValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if(null === $value) {
            return;
        }

        if(!is_array($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        $values = $value;
        $context = $this->context;
        foreach ($values as $index => $value) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('['.$index.']')
                ->validate($value[$index]);
        }
    }
}