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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ManagerInterface       $manager
     * @param LoggerFactoryInterface $factory
     */
    function __construct(ManagerInterface $manager, LoggerFactoryInterface $factory, LoggerInterface $logger = null)
    {
        $this->manager = $manager;
        $this->factory = $factory;
        $this->logger  = $logger == null ? new NullLogger() : $logger;
    }

    /**
     * @param ExecutionEvent $event
     * @return void
     */
    public function onPreExecute(ExecutionEvent $event)
    {
        $event->getContext()->set('manager', $this->manager);

        $this->logger->debug('Added runtime parameter "manager" to context', ['manager' => $this->manager]);

        $logger = $this->factory->create($event->getJob());

        $event->getContext()->set('logger', $logger);

        $this->logger->debug('Added runtime parameter "logger" to context', ['logger' => $logger]);
    }
}