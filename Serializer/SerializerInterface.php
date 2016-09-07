<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Serializer;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface SerializerInterface
{
    /**
     * @param mixed   $data
     * @param string  $format
     * @param SerializationContext|null $context
     *
     * @return string
     */
    public function serialize($data, $format, SerializationContext $context = null);

    /**
     * @param string  $data
     * @param string  $type
     * @param string  $format
     * @param DeserializationContext|null $context
     *
     * @return mixed
     */
    public function deserialize($data, $type, $format, DeserializationContext $context = null);
}