<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Validator\Job;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ConstraintProviderInterface
{
    /**
     * Returns the priority of the provider, which determines which provider is used for a job type.
     *
     * @return int Priority number (higher - preferred)
     */
    public function getPriority();

    /**
     * Returns an array of constraints to validate the parameters of a job.
     *
     * The number of the elements in the array should match the number of parameters a job can be created or updated with.
     *
     * Runtime parameters are not validated!
     *
     * Assuming the job does not use any runtime parameters the first element will be used to validate the first parameter of
     * the job, the seconds element will be used to validate the seconds parameter and so on. Use null to prevent validation
     * of a parameter.
     *
     * If your job uses runtime parameters they must be omitted in the returned constraints. If the method signature defines
     * three parameters where second parameter is a runtime parameter the returned array should only contain two elements,
     * where first element is used to validate first parameter and second element is used to validate third parameter.
     *
     * If the array contains less elements that the job defines parameters the remaining parameters  will not be validated.
     *
     * @param string $type The job type
     * @return array The constraints of a job type
     */
    public function getConstraints($type);
}