<?php

namespace Abc\Bundle\JobBundle\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @author Wojciech Ciolko <wojciech.ciolko@aboutcoders.com>
 */
abstract class AbstractList implements AbstractListInterface
{
    /**
     * @var array[Entity]
     * @Type("array")
     * @SerializedName("items")
     */
    protected $items;

    /**
     * @var int
     * @Type("integer")
     * @SerializedName("totalCount")
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