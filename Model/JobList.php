<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Model;

use Abc\Bundle\JobBundle\Model\AbstractList;
use JMS\Serializer\Annotation\Type;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobList extends AbstractList
{
    /**
     * @Type("array<Abc\Bundle\JobBundle\Model\Job>")
     */
    protected $items;
}