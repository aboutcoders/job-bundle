<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Locker;

use Abc\Bundle\ResourceLockBundle\Model\LockInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class NullLocker implements LockInterface
{
    /**
     * {@inheritdoc}
     */
    public function lock($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked($name, int $autoReleaseTime = 0)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function release($name)
    {
    }
}