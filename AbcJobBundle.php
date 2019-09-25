<?php

namespace Abc\JobBundle;

use Abc\JobBundle\DependencyInjection\Compiler\BuildJobProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AbcJobBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BuildJobProviderPass());
    }
}
