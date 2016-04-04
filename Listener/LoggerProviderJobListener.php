<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Listener;

use Abc\Bundle\JobBundle\Event\ExecutionEvent;
use Abc\Bundle\JobBundle\Job\Logger\FactoryInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LoggerProviderJobListener
{
    /** @var FactoryInterface */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    function __construct(FactoryInterface $factory)
    {
        $this->factory    = $factory;
    }

    /**
     * @param ExecutionEvent $event
     * @return void
     */
    public function onPreExecute(ExecutionEvent $event)
    {
        $event->getContext()->set('logger', $this->factory->create($event->getJob()));
    }
}