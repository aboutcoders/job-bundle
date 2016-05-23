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
use Abc\Bundle\JobBundle\Job\ProcessControl\Factory;
use Abc\ProcessControl\ControllerAwareInterface;

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
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var Factory
     */
    private $controllerFactory;

    /**
     * @param JobTypeRegistry $registry
     */
    function __construct(JobTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Register the manager that is passed to jobs implementing the ManagerAwareInterface
     *
     * @param ManagerInterface $manager
     * @return void
     * @see ManagerAwareInterface
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Register the process controller that is passed to jobs implementing the ControllerAwareInterface
     *
     * @param Factory $controllerFactory
     * @see Abc\ProcessControl\ControllerAwareInterface\ControllerAwareInterface
     */
    public function setControllerFactory(Factory $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
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
        $callableArray = $jobType->getCallable();

        $arguments = $this->resolveArguments($jobType, $context, $job->getParameters());

        if(is_array($callableArray) && $callable = $callableArray[0])
        {
            if($callable instanceof JobAwareInterface)
            {
                $callable->setJob($job);
            }

            if($callable instanceof ManagerAwareInterface)
            {
                $callable->setManager($this->manager);
            }
            
            if($callable instanceof ControllerAwareInterface)
            {
                $callable->setController($this->controllerFactory->create($job));
            }
        }

        return call_user_func_array($callableArray, $arguments);
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