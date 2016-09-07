<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Doctrine;

use Abc\Bundle\JobBundle\Doctrine\LogManager;
use Abc\Bundle\JobBundle\Entity\Log;
use Abc\Bundle\JobBundle\Test\DatabaseKernelTestCase;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class LogManagerTest extends DatabaseKernelTestCase
{
    /**
     * @var LogManager
     */
    private $subject;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->subject = new \Abc\Bundle\JobBundle\Entity\LogManager(
            $this->getEntityManager(),
            'Abc\Bundle\JobBundle\Logger\Entity\Log'
        );
    }

    public function testIsExpectedInstance()
    {
        $this->assertInstanceOf(LogManager::class, $this->subject);
    }

    public function testCRUD()
    {
        $log = $this->subject->create();
        $log->setChannel('Channel');
        $log->setLevel(200);
        $log->setLevelName('info');
        $log->setMessage('Message');
        $log->setDatetime(new \DateTime());
        $log->setContext(['context' => 'Context']);
        $log->setExtra(['extra' => 'Extra']);

        $this->subject->save($log);

        $this->getEntityManager()->clear();

        /** @var Log[] $logs */
        $logs = $this->subject->findAll();
        /** @var Log $log */
        $log = $logs[0];

        $this->assertCount(1, $logs);
        $this->assertEquals($log, $logs[0]);

        $this->subject->delete($log);

        $this->getEntityManager()->clear();

        $this->assertEmpty($this->subject->findAll());
    }
}