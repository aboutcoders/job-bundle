<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SingleParameterTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return is_array($value) ? $value[0] : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return array($value);
    }
}