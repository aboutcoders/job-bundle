<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

use Abc\Bundle\JobBundle\Model\Job;
use Abc\Bundle\JobBundle\Model\Schedule;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobBuilder
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $schedules = [];

    /**
     * @param string $type the job type
     */
    protected function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param $type
     * @return static
     */
    public static function create($type)
    {
        return new static($type);
    }

    /**
     * @param array $parameters
     * @return $this The current instance
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param string $type       The scheduler type
     * @param string $expression The scheduler expression
     * @return $this The current instance
     */
    public function addSchedule($type, $expression)
    {
        $this->schedules[] = [$type, $expression];

        return $this;
    }

    /**
     * @return JobInterface
     */
    public function build()
    {
        $job = new Job();
        $job->setType($this->type);
        $job->setParameters($this->parameters);
        foreach ($this->schedules as $schedule) {
            $job->addSchedule(new Schedule($schedule[0], $schedule[1]));
        }

        return $job;
    }
}