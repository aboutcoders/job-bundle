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
abstract class AbstractConstraintProvider implements ConstraintProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -1;
    }
}