<?php

namespace Abc\Bundle\JobBundle\Model;

/**
 * @author Wojciech Ciolko <wojciech.ciolko@aboutcoders.com>
 */
interface AbstractListInterface
{
    /**
     * @return \ArrayAccess
     */
    public function getItems();

    /**
     * @param \ArrayAccess $items
     */
    public function setItems($items);

    /**
     * @return int
     */
    public function getTotalCount();

    /**
     * @param int $totalCount
     */
    public function setTotalCount($totalCount);
}