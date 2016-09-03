<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle;

use Abc\Bundle\JobBundle\DependencyInjection\Compiler\ConfigurationCheckPass;
use Abc\Bundle\JobBundle\DependencyInjection\Compiler\ControllerConfigurationPass;
use Abc\Bundle\JobBundle\DependencyInjection\Compiler\LockerConfigurationPass;
use Abc\Bundle\JobBundle\DependencyInjection\Compiler\RegisterJobsPass;
use Abc\Bundle\JobBundle\DependencyInjection\Compiler\RegisterEventListenersPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AbcJobBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationCheckPass());
        $container->addCompilerPass(new ControllerConfigurationPass());
        $container->addCompilerPass(new LockerConfigurationPass());
        $container->addCompilerPass(new RegisterJobsPass());
        $container->addCompilerPass(new RegisterEventListenersPass());

        $this->addRegisterMappingsPass($container);
    }

    /**
     * Conditionally register mapping paths if bundle is configured to log to database
     *
     * @param ContainerBuilder $container
     * @see
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__ . '/Resources/config/doctrine-mapping') => 'Abc\Bundle\JobBundle\Logger\Entity',
        );

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('abc.job.model_manager_name'), 'abc.job.register_mapping.orm'));
    }
}