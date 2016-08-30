<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Adapter\Sonata\Fixtures;

use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TestIterator implements MessageIteratorInterface
{
    private $position = 0;
    private $array;

    public function __construct(array $elements)
    {
        $this->position = 0;
        $this->array    = $elements;
    }

    function rewind()
    {
        $this->position = 0;
    }

    function current()
    {
        return $this->array[$this->position];
    }

    function key()
    {
        return $this->position;
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->array[$this->position]);
    }
}