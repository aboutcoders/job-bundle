<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Request;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\JobTypeNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RequestBodyParameterConverter implements ParamConverterInterface
{
    /** @var JobTypeRegistry */
    protected $registry;
    /** @var ParamConverterInterface */
    protected $converter;
    /** @var  EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param JobTypeRegistry          $registry
     * @param ParamConverterInterface  $converter
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        JobTypeRegistry $registry,
        ParamConverterInterface $converter,
        EventDispatcherInterface $dispatcher)
    {
        $this->registry   = $registry;
        $this->converter  = $converter;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $type = $request->attributes->get('type');

        if($type == null)
        {
            throw new BadRequestHttpException('The job type must be defined');
        }

        try
        {
            $jobType = $this->registry->get($type);
        }
        catch(JobTypeNotFoundException $e)
        {
            throw new BadRequestHttpException(sprintf('A job of type "%s" is not defined', $e->getType()));
        }

        if($request->getContent() == null)
        {
            return true;
        }

        if(!is_array($jobType->getParameterTypes()) || count($jobType->getParameterTypes()) == 0)
        {
            throw new BadRequestHttpException(sprintf('The request body for jobs of type "%s" must be empty', $type));
        }

        $class = 'GenericArray<' . implode($jobType->getParameterTypes(), ',') . '>';
        $configuration->setClass($class);

        return $this->converter->apply($request, $configuration);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return true;
    }
}