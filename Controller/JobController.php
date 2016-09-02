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

use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\Bundle\JobBundle\Model\LogInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @RouteResource("Job")
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  description="Returns a collection of jobs",
     *  section="AbcJobBundle",
     *  requirements={},
     *  output="Abc\Bundle\JobBundle\Model\JobList",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when request is invalid",
     *   }
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page number of the result set")
     * @QueryParam(name="limit", requirements="\d+", default="10", description="Page size")
     * @QueryParam(name="sortCol", default="createdAt", description="Sort columns, valid values are [ticket|type|status|createdAt|terminatedAt]")
     * @QueryParam(name="sortDir", default="DESC", description="Sort direction, valid values are [ASC|DESC]")
     * @QueryParam(name="criteria", description="Search criteria defined as array, valid array keys are [ticket|type|status|createdAt|terminatedAt]")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return JobList
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $page       = $paramFetcher->get('page');
        $sortColumn = $paramFetcher->get('sortCol');
        $sortDir    = $paramFetcher->get('sortDir');
        $limit      = $paramFetcher->get('limit');
        $page       = (int)$page - 1;
        $offset     = ($page > 0) ? ($page) * $limit : 0;
        $criteria   = $paramFetcher->get('criteria');

        if (!$criteria) {
            $criteria = [];
        }

        $criteria = $this->filterCriteria($criteria);

        $manager = $this->getEntityManager();

        $entities = $manager->findBy($criteria, [$sortColumn => $sortDir], $limit, $offset);

        $count = $manager->findByCount($criteria);

        $list = new JobList();
        $list->setItems($entities);
        $list->setTotalCount($count);

        return $list;
    }

    /**
     * @ApiDoc(
     * description="Returns a job",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Job",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when node not found",
     *   }
     * )
     *
     * @param string $ticket
     * @return JobInterface
     */
    public function getAction($ticket)
    {
        try {
            return $this->getJobManager()->get($ticket);
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }


    /**
     * Adds a new job
     *
     * @ApiDoc(
     *   description="Adds a job",
     *   section="AbcJobBundle",
     *   output="Abc\Bundle\JobBundle\Model\Job",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Form validation error"
     *   }
     * )
     *
     * @ParamConverter("job", converter="abc.job.param_converter")
     * @Post("/jobs")
     *
     * @param Job $job
     * @return JobInterface
     */
    public function postAction(Job $job)
    {
        return $this->getJobManager()->add($job);
    }

    /**
     * Updates a job.
     *
     * @ApiDoc(
     *   description="Updates a job",
     *   section="AbcJobBundle",
     *   output="Abc\Bundle\JobBundle\Model\Job",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when validation fails",
     *     404 = "Returned when job not found"
     *   }
     * )
     *
     * @ParamConverter("job", converter="abc.job.param_converter")
     * @Put("/jobs")
     *
     * @param Job $job
     * @return JobInterface|Form
     */
    public function putAction(Job $job)
    {
        return $this->getJobManager()->update($job);
    }

    /**
     * @ApiDoc(
     * description="Cancels a job",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Job",
     * parameters={},
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
     *
     * @Post
     *
     * @param string $ticket
     * @return JobInterface
     */
    public function cancelAction($ticket)
    {
        try {
            return $this->getJobManager()->cancel($ticket);
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }

    /**
     * @ApiDoc(
     * description="Restarts a job",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Job",
     * parameters={},
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
     *
     * @Post
     *
     * @param string $ticket
     * @return JobInterface
     */
    public function restartAction($ticket)
    {
        try {
            return $this->getJobManager()->restart($ticket);
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }

    /**
     * Returns the logs of a job.
     *
     * @ApiDoc(
     * description="Returns the logs of a job",
     * section="AbcJobBundle",
     * output="array<Abc\Bundle\JobBundle\Model\Log>",
     * parameters={},
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
     *
     * @Get
     *
     * @param string $ticket
     * @return array|LogInterface[]
     */
    public function getLogsAction($ticket)
    {
        try {
            return $this->getJobManager()->getLogs($ticket);
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }


    /**
     * @param $criteria
     * @return array
     * @throws BadRequestHttpException If invalid status value is set in $criteria
     */
    protected function filterCriteria($criteria)
    {
        if (!is_array($criteria)) {
            throw new HttpException(400, 'Invalid search criteria');
        }

        if (isset($criteria['status'])) {
            try {
                $criteria['status'] = $this->getSerializer()->deserialize(json_encode($criteria['status']), Status::class, 'json');
            } catch (\Exception $e) {
                throw new BadRequestHttpException('Invalid status defined in criteria');
            }
        }

        return $criteria;
    }

    /**
     * @return ManagerInterface
     */
    protected function getJobManager()
    {
        return $this->get('abc.job.manager');
    }

    /**
     * @return JobManagerInterface
     */
    protected function getEntityManager()
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