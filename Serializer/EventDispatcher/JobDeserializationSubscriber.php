<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Serializer\EventDispatcher;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Model\Job;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobDeserializationSubscriber implements EventSubscriberInterface
{
    /**
     * @var JobTypeRegistry
     */
    private $registry;

    /**
     * @param JobTypeRegistry $registry
     */
    public function __construct(JobTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'serializer.pre_deserialize', 'method' => 'onPreDeserialize']
        ];
    }

    /**
     * Appends an array element to the parameters of a job that provides information types of the job parameters.
     *
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $type = $event->getType();

        if (isset($type['name']) && ($type['name'] == Job::class || is_subclass_of($type['name'], Job::class))) {

            $data = $event->getData();
            if (isset($data['type']) && isset($data['parameters']) && is_array($data['parameters']) && count($data['parameters']) > 0) {
                $jobType = $this->registry->get($data['type']);
                $serializableParameters = $jobType->getSerializableParameterTypes();

                if (count($serializableParameters) > 0) {
                    array_push($data['parameters'], ['abc.job.params' => $serializableParameters]);
                    $event->setData($data);
                }
            }
        }
    }
}