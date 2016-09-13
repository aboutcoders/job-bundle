<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RegisterConstraintProvidersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $validator;

    /**
     * @var string
     */
    private $tag;

    /**
     * @param string $validator Service name of the definition registry in processed container
     * @param string $tag      The tag name used for jobs
     */
    public function __construct($validator = 'abc.job.parameter_validator', $tag = 'abc.job.constraint_provider')
    {
        $this->validator = $validator;
        $this->tag       = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->validator) && !$container->hasAlias($this->validator)) {
            return;
        }

        $validator = $container->findDefinition($this->validator);
        foreach ($container->findTaggedServiceIds($this->tag) as $id => $tags) {
            $definition = $container->getDefinition($id);

            foreach ($tags as $tag) {
                $validator->addMethodCall('register', array($definition));
            }
        }
    }
}