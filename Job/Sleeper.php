<?php
/*
* This file is part of the wcm-backend package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job;

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\ProcessControl\Controller;
use Abc\ProcessControl\ControllerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * A test job to check if jobs can be cancelled at runtime
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Sleeper implements ControllerAwareInterface
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param                 $seconds
     * @param LoggerInterface $logger
     * @JobParameters({"integer", "@logger"})
     */
    public function sleep($seconds, LoggerInterface $logger)
    {
        do {
            sleep(1);
            $seconds--;
        } while ($seconds > 0 && !$this->controller->doExit());
    }
}