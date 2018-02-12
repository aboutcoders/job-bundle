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

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobTypeController extends BaseController
{
    /**
     * @Operation(
     *     tags={"AbcJobBundle"},
     *     summary="Returns a collection of job types",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(
     *              type="array",
     *              @SWG\Items(
     *                  type="string"
     *              )
     *         )
     *     )
     * )
     * @return Response
     */
    public function listAction()
    {
        return $this->serialize($this->getRegistry()->getTypeChoices());
    }

}