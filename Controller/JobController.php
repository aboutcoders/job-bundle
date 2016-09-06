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
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\JobList;
use JMS\Serializer\Exception\Exception;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Returns a collection of jobs",
     *     section="AbcJobBundle",
     *     filters={
     *          {"name"="page", "dataType"="integer", "required"=false, "requirement"="\d+", "default"="1", "description"="The page number of the result set"},
     *          {"name"="limit", "dataType"="integer", "required"=false, "requirement"="\d+", "default"="10", "description"="The page size"},
     *          {"name"="sortCol", "dataType"="string", "required"=false, "pattern"="(ticket|type|status|createdAt|terminatedAt)", "default"="createdAt", "description"="The sort column"},
     *          {"name"="sortDir", "dataType"="string", "required"=false, "pattern"="(ASC|DESC)", "default"="DESC", "description"="The sort direction"},
     *          {"name"="criteria", "dataType"="map", "required"=false, "default"="[]", "description"="The search criteria defined as associative array"},
     *     },
     *     output="Abc\Bundle\JobBundle\Model\JobList",
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when request is invalid"
     *     }
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $page       = $request->query->get('page', 1);
        $sortColumn = $request->query->get('sortCol', 'createdAt');
        $sortDir    = $request->query->get('sortDir', 'DESC');
        $limit      = $request->query->get('limit', 10);
        $page       = (int)$page - 1;
        $offset     = ($page > 0) ? ($page) * $limit : 0;
        $criteria   = $request->query->get('criteria', array());

        $criteria = $this->filterCriteria($criteria);

        $manager = $this->getJobManager();

        $entities = $manager->findBy($criteria, [$sortColumn => $sortDir], $limit, $offset);

        $count = $manager->findByCount($criteria);

        $list = new JobList();
        $list->setItems($entities);
        $list->setTotalCount($count);

        return $this->serialize($list);
    }

    /**
     * @ApiDoc(
     * description="Returns a job",
     * section="AbcJobBundle",
     * requirements={
     *      {"name"="ticket", "dataType"="string", "required"=true, "description"="The job ticket"},
     * },
     * output="Abc\Bundle\JobBundle\Model\Job",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when node not found",
     *   }
     * )
     *
     * @param string $ticket
     * @return Response
     */
    public function getAction($ticket)
    {
        try {
            return $this->serialize($this->getManager()->get($ticket));
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }

    /**
     * Adds a new job
     *
     * @ApiDoc(
     *  description="Adds a job",
     *  section="AbcJobBundle",
     *  input="Abc\Bundle\JobBundle\Model\Job",
     *  input="Abc\Bundle\JobBundle\Model\Job",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Form validation error"
     *   }
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $job = $this->deserializeJob($request);

        return $this->serialize($this->getManager()->add($job));
    }

    /**
     * Updates a job.
     *
     * @ApiDoc(
     * description="Updates a job",
     * section="AbcJobBundle",
     * input="Abc\Bundle\JobBundle\Model\Job",
     * output="Abc\Bundle\JobBundle\Model\Job",
     * statusCodes = {
     *  200 = "Returned when successful",
     *  400 = "Returned when validation fails",
     *  404 = "Returned when job not found"
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        $job = $this->deserializeJob($request);

        return $this->serialize($this->getManager()->update($job));
    }

    /**
     * @ApiDoc(
     * description="Cancels a job",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Job",
     * requirements={
     *      {"name"="ticket", "dataType"="string", "required"=true, "description"="The job ticket"},
     * },
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
     *
     * @param string $ticket
     * @return Response
     */
    public function cancelAction($ticket)
    {
        try {
            return $this->serialize($this->getManager()->cancel($ticket));
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }

    /**
     * @ApiDoc(
     * description="Restarts a job",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Job",
     * requirements={
     *      {"name"="ticket", "dataType"="string", "required"=true, "description"="The job ticket"},
     * },
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
     *
     * @param string $ticket
     * @return Response
     */
    public function restartAction($ticket)
    {
        try {
            return $this->serialize($this->getManager()->restart($ticket));
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
     * requirements={
     *      {"name"="ticket", "dataType"="string", "required"=true, "description"="The job ticket"},
     * },
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
     *
     * @param string $ticket
     * @return Response
     */
    public function logsAction($ticket)
    {
        try {
            return $this->serialize($this->getManager()->getLogs($ticket));
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }

    /**
     * @param Request $request
     * @return JobInterface|mixed
     * @throws UnsupportedMediaTypeHttpException
     * @throws BadRequestHttpException
     */
    protected function deserializeJob(Request $request)
    {
        try {
            return $this->getSerializer()->deserialize(
                json_encode($request->request->all(), true),
                Job::class,
                $request->getContentType()
            );
        } catch (UnsupportedFormatException $e) {
            throw new UnsupportedMediaTypeHttpException($e->getMessage(), $e);
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
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
}