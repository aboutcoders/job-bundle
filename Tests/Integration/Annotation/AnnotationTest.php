<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Annotation;

use Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AnnotationTest extends KernelTestCase
{
    /** @var Application */
    private $application;
    /** @var ContainerInterface */
    private $container;
    /** @var MetadataFactoryInterface */
    private $metadataFactory;
    /** @var ClassMetadata */
    private $classMetadata;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();

        $this->container   = static::$kernel->getContainer();
        $this->application = new Application(static::$kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        $this->metadataFactory = $this->container->get('abc.job.metadata_factory');

        /** @var ClassMetadata $classMetadata */
        $this->classMetadata = $this->metadataFactory->getMetadataForClass('Abc\Bundle\JobBundle\Tests\Fixtures\Annotation\TestJob')->getRootClassMetadata();
    }

    public function testMethodWithSingleParameters()
    {
        $this->assertEquals(array('string'), $this->classMetadata->getMethodArgumentTypes('methodWithSingleParameters'));
    }

    public function testMethodWithMultipleParameters()
    {
        $this->assertEquals(array('string', 'boolean'), $this->classMetadata->getMethodArgumentTypes('methodWithMultipleParameters'));
    }

    public function testMethodWithResponse()
    {
        $this->assertEquals('string', $this->classMetadata->getMethodReturnType('methodWithResponse'));
    }
} 