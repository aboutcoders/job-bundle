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
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @RouteResource("Type")
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TypeController extends FOSRestController
{

    /**
     * @return array
     *
     * @ApiDoc(
     * description="Returns a collection of job types",
     * section="AbcJobBundle",
     * requirements={},
     * output="array<String>",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     */
    public function cgetAction()
    {
        $types = array();
        foreach ($this->getRegistry()->all() as $jobType) {
            $types[] = $jobType->getName();
        }

        return $types;
    }

    /**
     * @return JobTypeRegistry
     */
    protected function getRegistry()
    {
        return $this->get('abc.job.registry');
    }
}