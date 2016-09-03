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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeController extends BaseController
{
    /**
     * @ApiDoc(
     * description="Returns a collection of job types",
     * section="AbcJobBundle",
     * requirements={},
     * output="array<string>",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @return Response
     */
    public function listAction()
    {
        return $this->serialize($this->getRegistry()->getTypeChoices());
    }

}