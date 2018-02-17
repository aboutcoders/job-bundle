<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Locker;

use Abc\Bundle\JobBundle\Locker\NullLocker;
use Abc\Bundle\ResourceLockBundle\Model\LockInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class NullLockerTest extends TestCase
{
    /**
     * @var NullLocker
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subject = new NullLocker();
    }

    public function testNullLockerDoesNothing()
    {
        $this->assertInstanceOf(LockInterface::class, $this->subject);
        $this->assertFalse($this->subject->isLocked('foobar'));
        $this->subject->release('barfoo');
        $this->subject->lock('foobar');
    }
}
