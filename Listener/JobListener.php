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
use Abc\Bundle\JobBundle\Job\ManagerInterface;

/**
 * Registers the default runtime parameters "manager" and "logger".
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobListener
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param ManagerInterface $manager
     * @param FactoryInterface $factory
     */
    function __construct(ManagerInterface $manager, FactoryInterface $factory)
    {
        $this->manager = $manager;
        $this->factory    = $factory;
    }

    /**
     * @param ExecutionEvent $event
     * @return void
     */
    public function onPreExecute(ExecutionEvent $event)
    {
        $event->getContext()->set('manager', $this->manager);
        $event->getContext()->set('logger', $this->factory->create($event->getJob()));
    }
}