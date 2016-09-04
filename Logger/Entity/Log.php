<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Logger\Entity;

use Abc\Bundle\JobBundle\Entity\Log as BaseLog;

/**
 * Only reason this class is defined here is on order to register the mapping (Doctrine, MongodDB, CouchDB) optionally
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Log extends BaseLog
{
}