<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Report;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface EraserInterface
{
    /**
     * Erases all reports with the given tickets.
     *
     * @param array $tickets An array of strings each referencing a ticket
     */
    public function eraseByTickets(array $tickets);

    /**
     * Erases all reports with the given tickets.
     *
     * @param array $types An array of strings each referencing a types
     */
    public function eraseByTypes(array $types);

    /**
     * Erase reports that are older than the given number of days.
     *
     * @param int $days
     * @param array $types Optional, the types of reports to erase (if not specified all reports are deleted)
     */
    public function eraseByAge($days, array $types = array());
}