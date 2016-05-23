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
use Abc\ProcessControl\ControllerAwareInterface;
use Abc\ProcessControl\ControllerInterface;
use Psr\Log\LoggerInterface;

/**
 * A test job to check if jobs can be cancelled at runtime
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Sleeper implements ControllerAwareInterface
{
    /**
     * @var ControllerInterface
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setController(ControllerInterface $controller)
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
        $logger->info('start sleeping for {seconds}', ['seconds' => $seconds]);

        $start = time();

        do {

            sleep(1);
            $seconds--;

        } while ($seconds > 0 && !$this->controller->doExit());

        $logger->info('stopped sleeping after {seconds}', ['seconds' => time() - $start]);
    }
}