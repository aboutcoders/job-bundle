<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\DependencyInjection\Compiler;

use \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass as BaseRegisterListenersPass;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RegisterEventListenersPass extends BaseRegisterListenersPass
{
    /**
     * @param string $dispatcherService Service name of the event dispatcher in processed container
     * @param string $listenerTag       Tag name used for listener
     * @param string $subscriberTag     Tag name used for subscribers
     */
    public function __construct($dispatcherService = 'event_dispatcher', $listenerTag = 'abc.job.event_listener', $subscriberTag = 'abc.job.event_subscriber')
    {
        parent::__construct($dispatcherService, $listenerTag, $subscriberTag);
    }
}