<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ManagerAwareInterface
{
    /**
     * @param ManagerInterface $manager
     * @return void
     */
    public function setManager(ManagerInterface $manager);
}