<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Controller;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class BaseController extends Controller
{
    /**
     * @param mixed $data
     * @return Response
     */
    protected function serialize($data)
    {
        return new Response($this->getSerializer()->serialize($data, 'json'));
    }

    /**
     * @return JobTypeRegistry
     */
    protected function getRegistry()
    {
        return $this->get('abc.job.registry');
    }

    /**
     * @return ManagerInterface
     */
    protected function getManager()
    {
        return $this->get('abc.job.manager');
    }

    /**
     * @return JobManagerInterface
     */
    protected function getJobManager()
    {
        return $this->get('abc.job.job_manager');
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->get('jms_serializer');
    }
}