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
use Abc\Bundle\JobBundle\Model\JobList;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @RouteResource("Job")
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobController extends FOSRestController
{

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     * @QueryParam(name="limit", requirements="\d+", default="10", description="Page size.")
     * @QueryParam(name="sortCol", default="createdAt", description="Sort columns, valid values are (ticket|type|status|createdAt|terminatedAt)")
     * @QueryParam(name="sortDir", default="DESC", description="Sort direction, valid values are (ASC|DESC)")
     * @QueryParam(name="criteria", description="Search criteria, valid values are (ticket|type|status|createdAt|terminatedAt)")
     * @return array
     *
     * @ApiDoc(
     *  description="Returns a collection of Jobs",
     *  section="AbcJobBundle",
     *  requirements={},
     *  filters={
     *      {
     *          "name"="page",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Page of results"
     *      },
     *      {
     *          "name"="limit",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Page size"
     *      },
     *      {
     *          "name"="sortCol",
     *          "dataType"="string",
     *          "description"="Sort columns, possible values are (ticket|type|status|createdAt|terminatedAt)"
     *      },
     *      {
     *          "name"="sortDir",
     *          "dataType"="string",
     *          "description"="Sort direction, possible values are (ASC|DESC)"
     *      },
     *      {
     *          "name"="criteria",
     *          "dataType"="array",
     *          "description"="Searching criteria defined as array, possible search fields are (ticket|type|status|createdAt|terminatedAt)"
     *      }
     *  },
     * output="array<Abc\Bundle\JobBundle\Model\JobList>",
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
     * description="Returns a job with the given ticket",
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
     * Create an entity
     *
     * @ApiDoc(
     *   section="AbcJobBundle",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Form validation error"
     *   }
     * )
     *
     * @param Request $request the request object
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $type = $request->get('type');
        if (!$this->getRegistry()->has($type)) {
            throw $this->createNotFoundException(sprintf('A job of type "%s" not found', $type));
        }

        $formType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? JobType::class : 'abc_job';

        $form = $this->createNamedForm('', $formType, $this->getEntityManager()->create($type));

        return $this->processForm($form, $request);
    }

    /**
     * @param string $ticket
     * @return JobInterface
     *
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
     */
    public function cancelAction($ticket)
    {
        try {
            return $this->getJobManager()->cancelJob($ticket);
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
            return $this->getJobManager()->getJobLogs($ticket);
        } catch (TicketNotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Job with ticket %s not found', $ticket), $e);
        }
    }

    /**
     * @param Form    $form
     * @param Request $request
     * @return object Entity
     */
    protected function processForm(Form $form, $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->getJobManager()->add($form->getData());
        } else {
            return $form;
        }
    }

    /**
     * @param $criteria
     * @return array
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
     * @return JobHelper
     */
    protected function getJobHelper()
    {
        return $this->get('abc.job.helper');
    }

    /**
     * @return JobTypeRegistry
     */
    protected function getRegistry()
    {
        return $this->get('abc.job.registry');
    }

    /**
     * @param       $name
     * @param       $type
     * @param null  $data
     * @param array $options
     * @return mixed
     */
    private function createNamedForm($name, $type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createNamed($name, $type, $data, $options);
    }
}