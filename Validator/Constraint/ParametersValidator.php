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

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Validator\Job\ConstraintProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ParametersValidator extends ConstraintValidator
{
    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @var ConstraintProviderInterface[]
     */
    private $providers = array();

    /**
     * @var array
     */
    private $constraints;

    /**
     * @param JobTypeRegistry $registry
     */
    public function __construct(JobTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($this->constraints)) {
            $this->initializeConstraints();
        }

        if (!$constraint->type) {
            throw new ConstraintDefinitionException('"type" must be specified on constraint Parameters');
        }

        if (!isset($this->constraints[$constraint->type])) {
            return;
        }

        if (null !== $value && !is_array($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        $values = $value;
        $validatedValues = [];
        foreach ($values as $index => $value) {
            if (isset($this->constraints[$constraint->type]) && isset($this->constraints[$constraint->type][$index])) {
                $this->context->getValidator()
                    ->inContext($this->context)
                    ->atPath('[' . $index . ']')
                    ->validate($value, $this->constraints[$constraint->type][$index]);
                $validatedValues[] = $index;
            }

            if (is_object($value)) {
                $this->context->getValidator()
                    ->inContext($this->context)
                    ->atPath('[' . $index . ']')
                    ->validate($value);
            }
        }

        if (isset($this->constraints[$constraint->type])) {
            foreach ($this->constraints[$constraint->type] as $index => $constraint) {
                if(!in_array($index, $validatedValues)) {
                    $this->context->getValidator()
                        ->inContext($this->context)
                        ->atPath('[' . $index . ']')
                        ->validate(null, $constraint);
                }
            }
        }
    }

    /**
     * @param ConstraintProviderInterface $provider
     * @return void
     */
    public function register(ConstraintProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    private function initializeConstraints()
    {
        $this->constraints = array();
        $priority          = [];
        foreach ($this->providers as $provider) {
            foreach ($this->registry->getTypeChoices() as $type) {
                $constraints = $provider->getConstraints($type);
                if (is_array($constraints) && count($constraints) > 0) {
                    if (!isset($this->constraints[$type]) || $provider->getPriority() > $priority[$type]) {
                        $this->constraints[$type] = $constraints;
                        $priority[$type]          = $provider->getPriority();
                    }
                }
            }
        }
    }
}