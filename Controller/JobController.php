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

use Abc\Bundle\JobBundle\Form\Type\JobType;
use Abc\Bundle\JobBundle\Job\Exception\TicketNotFoundException;
use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Job\JobHelper;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @RouteResource("Job")
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobController extends FOSRestController
{

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page number of the result set")
     * @QueryParam(name="limit", requirements="\d+", default="10", description="Page size")
     * @QueryParam(name="sortCol", default="createdAt", description="Sort columns, valid values are [ticket|type|status|createdAt|terminatedAt]")
     * @QueryParam(name="sortDir", default="DESC", description="Sort direction, valid values are [ASC|DESC]")
     * @QueryParam(name="criteria", description="Search criteria defined as array, valid array keys are [ticket|type|status|createdAt|terminatedAt]")
     * @return JobList
     *
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
     * @param string $ticket
     * @return JobInterface
     *
     * @ApiDoc(
     * description="Returns a job",
     * section="AbcJobBundle",
     * output="Abc\Bundle\JobBundle\Model\Job",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when node not found",
     *   }
     * )
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
     * Adds a job
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
     * @param Request $request the request object
     * @return JobInterface|Form
     */
    public function postAction(Request $request)
    {
        $job = $this->getEntityManager()->create($request->get('type'));

        $form = $this->createNamedForm('', $this->getJobFormType(), $job);

        return $this->processForm($form, $request);
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
     * @param string  $ticket
     * @param Request $request the request object
     * @return JobInterface|Form
     */
    public function putAction($ticket, Request $request)
    {
        if(!$job = $this->getJobManager()->get($ticket)){
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket));
        }

        $form = $this->createNamedForm('', $this->getJobFormType(), $job, [
            'method' => 'PUT'
        ]);

        return $this->processForm($form, $request, $ticket);
    }

    /**
     * @Post
     *
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
     * @Post
     *
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
     * @param string $ticket
     * @return string
     *
     * @Get
     *
     * @ApiDoc(
     * description="Returns the logs of a job",
     * section="AbcJobBundle",
     * output="string",
     * parameters={},
     * statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when job not found",
     *   }
     * )
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
     * @param Form        $form
     * @param Request     $request
     * @param string|null $ticket
     * @return JobInterface|Form The added job if validation succeeded, otherwise the form
     */
    protected function processForm(Form $form, $request, $ticket = null)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ('POST' === $request->getMethod()) {
                return $this->getJobManager()->add($form->getData());
            } elseif ('PUT' === $request->getMethod()) {
                $job = $form->getData();
                $job->setTicket($ticket);

                return $this->getJobManager()->update($job);
            }

        } else {
            return $form;
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

        foreach ($criteria as $key => $value) {
            if ($criteria[$key] == '') {
                unset ($criteria[$key]);
            }
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
        return $this->get('abc.job.job_entity_manager');
    }

    /**
     * @return JobHelper
     */
    protected function getJobHelper()
    {
        return $this->get('abc.job.helper');
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->get('jms_serializer');
    }

    /**
     * @param       $name
     * @param       $type
     * @param null  $data
     * @param array $options
     * @return Form
     */
    private function createNamedForm($name, $type, $data = null, array $options = [])
    {
        return $this->container->get('form.factory')->createNamed($name, $type, $data, $options);
    }

    /**
     * @return string
     */
    private function getJobFormType()
    {
        return method_exists(AbstractType::class, 'getBlockPrefix') ? JobType::class : 'abc_job';
    }
}