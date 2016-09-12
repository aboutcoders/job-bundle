<?php

namespace Abc\Bundle\JobBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 * @author Wojciech Ciolko <wojciech.ciolko@aboutcoders.com>
 */
abstract class AbstractList implements AbstractListInterface
{
    /**
     * @JMS\Type("array")
     * @JMS\SerializedName("items")
     * @var array[Entity]
     */
    protected $items;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("totalCount")
     * @var int
     */
    protected $totalCount;

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalTotalCount
     */
    public function setTotalCount($totalTotalCount)
    {
        $this->totalCount = $totalTotalCount;
    }
}