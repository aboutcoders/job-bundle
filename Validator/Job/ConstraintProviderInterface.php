<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Parameter;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ConstraintProviderInterface
{
    /**
     * Returns an array of constraints that are used to validate the parameters of a job.
     *
     * The number of the elements in the array should match the number of parameters the job can be invoked with. The first element
     * will be used to validate the first parameter of the job, the seconds element will be used to validate the seconds parameter
     * and so on.
     *
     * Use null to prevent validation of a parameter. If the array contains less elements that the job defines parameters the remaining
     * parameters  will not be validated.
     *
     * @param string $type The job type
     * @return array|null An array of elements of type Constraint or Constraint[]
     */
    public function getConstraints($type);
}