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

use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class GenericArrayHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        $formats = array('json');

        foreach($formats as $format)
        {
            $methods[] = array(
                'type' => 'GenericArray',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'method' => 'deserializeArray'
            );
        }

        return $methods;
    }

    /**
     * @param VisitorInterface           $visitor
     * @param                            $data
     * @param array                      $type
     * @param Context                    $context
     * @return array|null
     */
    public function deserializeArray(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        if(null === $data)
        {
            return null;
        }

        if(!is_array($data) || count($data) > count($type['params']))
        {
            throw new RuntimeException('Type defines less element types than elements given');
        }

        for($i = 0; $i < count($data); $i++)
        {
            $data[$i] = $context->getNavigator()->accept($data[$i], $type['params'][$i], $context);
        }

        // WORKAROUND
        //
        // $visitor->setNavigator sets the result to null. Thereby the return value of this method is returned by the serializer
        // instead of the result produced by $visitor->getNavigator()->accept()
        $visitor->setNavigator($context->getNavigator());

        return $data;
    }
}