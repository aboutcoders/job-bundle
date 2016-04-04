<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class DatabaseWebTestCase extends WebTestCase
{
    /**
     * @var Application
     */
    protected static $application;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::runCommand('doctrine:schema:drop', array("--force" => true));
        self::runCommand('doctrine:schema:update', array("--force" => true));
    }

    /**
     * @param string $command
     * @param array  $options
     * @return int 0 if everything went fine, or an error code
     * @throws \Exception
     */
    protected static function runCommand($command, array $options = array())
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options       = array_merge($options, array('command' => $command));

        return self::getApplication()->run(new ArrayInput($options));
    }

    /**
     * @return Application
     */
    protected static function getApplication()
    {
        if(null === self::$application)
        {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
            self::$application->setCatchExceptions(false);
        }

        return self::$application;
    }
}