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
use Psr\Log\LoggerAwareInterface;

/**
 * Invokes a job.
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
        $jobType       = $this->registry->get($job->getType());
        $callableArray = $jobType->getCallable();
        $parameters    = static::resolveParameters($jobType, $context, $job->getParameters());

        if (is_array($callableArray) && $callable = $callableArray[0]) {
            if ($callable instanceof JobAwareInterface) {
                $callable->setJob($job);
            }

            if ($callable instanceof ManagerAwareInterface) {
                $callable->setManager($this->manager);
            }

            if ($callable instanceof ControllerAwareInterface) {
                $callable->setController($this->controllerFactory->create($job));
            }

            if ($callable instanceof LoggerAwareInterface && $context->has('abc.logger')) {
                $callable->setLogger($context->get('abc.logger'));
            }
        }

        return call_user_func_array($callableArray, $parameters);
    }

    /**
     * @param JobTypeInterface $jobType
     * @param ContextInterface $context
     * @param array|null       $parameters
     * @return array
     */
    public static function resolveParameters(JobTypeInterface $jobType, ContextInterface $context, $parameters = null)
    {
        $result     = array();
        $parameters = $parameters == null ? array() : $parameters;
        foreach ($jobType->getParameterTypes() as $parameterType) {
            if (0 === strpos($parameterType, '@')) {
                $key      = substr($parameterType, 1);
                $result[] = $context->has($key) ? $context->get($key) : null;
            } else {
                $result[] = array_shift($parameters);
            }
        }

        return $result;
    }
}