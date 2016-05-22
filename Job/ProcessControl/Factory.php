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

use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\JobManagerInterface;

/**
 * The default factory.
 *
 * This factory creates process controllers of type Controller
 *
 * @see Controller
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
     * Factory constructor.
     *
     * @param JobManagerInterface $manager
     * @param int                 $interval The minimum number of seconds that must have been passed between two refresh operations
     */
    public function __construct(JobManagerInterface $manager, $interval)
    {
        $this->manager  = $manager;
        $this->interval = $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function create(JobInterface $job)
    {
        return new Controller($job, $this->manager , $this->interval);
    }
}