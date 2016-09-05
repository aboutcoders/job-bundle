<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Serializer\Handler;

use Abc\Bundle\JobBundle\Job\JobParameterArray;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobParameterArrayHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $formats = ['json'];
        $methods = [];

        foreach ($formats as $format) {
            $methods[] = [
                'type'      => JobParameterArray::class,
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => $format,
                'method'    => 'serializeJobParameterArray'
            ];

            $methods[] = [
                'type'      => JobParameterArray::class,
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => $format,
                'method'    => 'deserializeJobParameterArray'
            ];
        }

        return $methods;
    }

    /**
     * @param VisitorInterface $visitor
     * @param array            $data
     * @param array            $type
     * @param Context          $context
     * @return mixed
     */
    public function serializeJobParameterArray(VisitorInterface $visitor, array $data, array $type, Context $context)
    {
        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param VisitorInterface $visitor
     * @param mixed            $data
     * @param array            $type
     * @param Context          $context
     * @return array|null
     */
    public function deserializeJobParameterArray(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        $deserializeJob = false;
        if (is_array($data) && count($data) > 0) {
            // if $type['params'] is not set this means, that a job is being deserialized, so we check if the JobDeserializationSubscriber set the type of params at the end of the $data array
            if (count($type['params']) == 0 && is_array(end($data)) && in_array('abc.job.params', array_keys(end($data)))) {

                $type['params'] = $this->extractParamTypes($data);
                $deserializeJob = true;
            }
        }

        if (count($data) > count($type['params'])) {
            throw new RuntimeException(sprintf('Invalid job parameter, array contains more elements that defined (%s)', implode(',', $type['params'])));
        }

        $result = [];
        for ($i = 0; $i < count($data); $i++) {
            if (!is_array($type['params'][$i])) {
                $type['params'][$i] = [
                    'name'   => $type['params'][$i],
                    'params' => array()
                ];
            }

            $result[$i] = $context->accept($data[$i], $type['params'][$i]);
        }

        if (!$deserializeJob) {
            /**
             * Since serializer always returns the result of $context->accept unless visitor result is empty,
             * we have to make sure that the visitor result is null in case only root is type JobParameterArray::class
             *
             * @see Serializer::handleDeserializeResult()
             */
            $visitor->setNavigator($context->getNavigator());
        }

        return $result;
    }

    private function extractParamTypes(&$data)
    {
        $paramTypeArray = array_pop($data);

        return array_pop($paramTypeArray);
    }
}