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
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Logger\LoggerFactoryInterface;

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
     * @var LoggerFactoryInterface
     */
    private $factory;

    /**
     * @param ManagerInterface       $manager
     * @param LoggerFactoryInterface $factory
     */
    function __construct(ManagerInterface $manager, LoggerFactoryInterface $factory)
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