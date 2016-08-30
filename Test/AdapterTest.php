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

use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
abstract class AdapterTest extends DatabaseKernelTestCase
{
    public function setUp()
    {
        $this->setKernelOptions(['environment' => $this->getEnvironment()]);
        parent::setUp();
    }

    public function testProduceAndConsume()
    {
        $ticket = $this->getJobManager()->addJob('log', array('message'));

        $this->processJobs();

        $this->assertEquals(Status::PROCESSED(), $this->getJobManager()->get($ticket)->getStatus());
    }

    /**
     * @return string The name of the environment
     */
    public abstract function getEnvironment();

    /**
     * @return ManagerInterface
     */
    protected function getJobManager()
    {
        return $this->getContainer()->get('abc.job.manager');
    }

    /**
     * @return void
     */
    protected function processJobs()
    {
        /**
         * @var ConsumerInterface $consumer
         */
        $consumer = $this->getContainer()->get('abc.job.consumer');
        $consumer->consume('default', [
            'stop-when-empty' => true
        ]);
    }
}