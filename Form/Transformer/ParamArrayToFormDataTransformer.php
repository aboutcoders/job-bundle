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
 * A configurable transformer to transform an array of job parameters to the data required by a job form.
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ParamArrayToFormDataTransformer implements DataTransformerInterface
{
    /**
     * @var array
     */
    private $fieldKeyMap = [];

    /**
     * Maps a field to the parameter array passed to the
     *
     * @param string $field The field name
     * @param int    $key   The corresponding key of the field/value within the parameter array passed to the job
     */
    public function mapField($field, $key)
    {
        $this->fieldKeyMap[$field] = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (is_array($value)) {
            if (count($this->fieldKeyMap) > 0) {
                $formData = [];
                foreach ($this->fieldKeyMap as $field => $key) {
                    $formData[$field] = $value[$key];
                }

                $value = $formData;

            } elseif (count($value) == 1) {
                /**
                 * if the array contains only one element and no field mapper is registered, we assume that
                 * the first element is an object that is mapped to form type. So we just return this first
                 * element.
                 */
                $value = $value[0];
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (is_object($value)) {
            $value = [$value];
        } elseif (is_array($value) && count($this->fieldKeyMap) > 0) {
            $paramArray = [];
            foreach ($value as $field => $paramValue) {
                $paramArray[$this->getKey($field)] = $paramValue;
            }

            $value = $paramArray;
        }

        return $value;
    }

    /**
     * @param string $field
     * @return int|null
     */
    private function getKey($field)
    {
        return isset($this->fieldKeyMap[$field]) ? $this->fieldKeyMap[$field] : null;
    }
}