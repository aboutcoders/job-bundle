<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Job;

use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConnectionTest extends DatabaseKernelTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->setEntityManagerNames(['default', 'abc_job_processing']);
        $this->setKernelOptions(['environment' => 'dedicated_connection']);
        parent::setUp();
    }

    public function testWithDedicatedConnection()
    {
        $job = $this->getJobManager()->addJob('throw_dbal_exception');

        $this->processJobs();

        $this->getEntityManager()->clear();

        $this->assertEquals(Status::ERROR(), $job->getStatus());
    }

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