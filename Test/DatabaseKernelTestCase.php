<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Test;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class DatabaseKernelTestCase extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $entityManagerNames = [];

    /**
     * @var Application
     */
    private $application;

    /**
     * @var array
     */
    private $kernelOptions = [];

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel($this->kernelOptions);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();;

        $this->application = new Application(static::$kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        if (count($this->entityManagerNames) > 0) {
            foreach ($this->entityManagerNames as $name) {
                $this->runConsole("doctrine:schema:drop", ['--force' => true, '--em' => $name]);
                $this->runConsole("doctrine:schema:update", ['--force' => true, '--em' => $name]);
            }
        } else {
            $this->runConsole("doctrine:schema:drop", array("--force" => true));
            $this->runConsole("doctrine:schema:update", array("--force" => true));
        }
    }

    /**
     * Set the names of the entity managers that are used
     *
     * @param array $names
     */
    public function setEntityManagerNames(array $names)
    {
        $this->entityManagerNames = $names;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (!is_null($this->em)) {
            $this->em->close();
        }
    }

    public function setKernelOptions(array $options)
    {
        $this->kernelOptions = $options;
    }

    /**
     * @return Application
     */
    protected function getApplication()
    {
        return $this->application;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return static::$kernel->getContainer();
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param string $command The command name (e.g. doctrine:schema:drop)
     * @param array  $options The command options
     * @return int 0 if everything went fine, or an error code
     * @throws \Exception
     */
    protected function runConsole($command, array $options = array())
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options       = array_merge($options, array('command' => $command));

        return $this->application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }
}