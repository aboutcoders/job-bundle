<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\ProcessControl;

use Abc\Bundle\JobBundle\Job\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;
use Abc\ProcessControl\ChainController;
use Abc\ProcessControl\ControllerInterface;

/**
 * The default factory.
 *
 * This factory creates process controllers of type Controller
 *
 * @see    Controller
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Factory implements FactoryInterface
{
    /**
     * @var JobManagerInterface
     */
    protected $manager;

    /**
     * @var int
     */
    protected $interval;

    /**
     * @var array ControllerInterface[]
     */
    protected $additionalController = [];

    /**
     * @param JobManagerInterface $manager
     * @param int                 $interval The minimum number of seconds that must have been passed between two refresh operations
     */
    public function __construct(JobManagerInterface $manager, $interval)
    {
        $this->manager  = $manager;
        $this->interval = $interval;
    }

    /**
     * Adds an additional controller.
     * 
     * If additional controllers are registered an instance of ChainController is created
     * containing the all the additional controller plus the one which is created by this
     * factory by default.
     *
     * @param ControllerInterface $controller
     * @return void
     */
    public function addController(ControllerInterface $controller)
    {
        $this->additionalController[] = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function create(JobInterface $job)
    {
        $controller = new Controller($job, $this->manager, $this->interval);

        if (count($this->additionalController) > 0) {
            $controller = new ChainController(array_merge($this->additionalController, [$controller]));
        }

        return $controller;
    }
}