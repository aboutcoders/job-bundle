<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

use Abc\Bundle\JobBundle\Job\Context\ContextInterface;

/**
 * Invokes the callable registered for a certain job type
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Invoker
{
    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @param JobTypeRegistry $registry
     */
    function __construct(JobTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Invokes the job.
     *
     * @param JobInterface     $job
     * @param ContextInterface $context
     * @return mixed
     * @throws JobTypeNotFoundException
     */
    public function invoke(JobInterface $job, ContextInterface $context)
    {
        $jobType  = $this->registry->get($job->getType());
        $callable = $jobType->getCallable();

        $arguments = $this->resolveArguments($jobType, $context, $job->getParameters());

        if(is_array($callable) && $callable[0] instanceof JobAwareInterface)
        {
            /** @var JobAwareInterface $jobAwareCallable */
            $jobAwareCallable = $callable[0];
            $jobAwareCallable->setJob($job);
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * @param JobTypeInterface $jobType
     * @param ContextInterface $context
     * @param array            $parameters
     * @return array
     */
    protected function resolveArguments(JobTypeInterface $jobType, ContextInterface $context, $parameters)
    {
        $arguments = array();
        foreach($jobType->getParameterTypes() as $parameterType)
        {
            if(0 === strpos($parameterType, '@'))
            {
                $key         = substr($parameterType, 1);
                $arguments[] = $context->has($key) ? $context->get($key) : null;
            }
            else
            {
                $arguments[] = array_shift($parameters);
            }
        }

        return $arguments;
    }
}