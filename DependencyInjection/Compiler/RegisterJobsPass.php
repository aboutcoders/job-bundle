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

use Monolog\Logger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class RegisterJobsPass implements CompilerPassInterface
{

    /**
     * @var string
     */
    private $registryService;

    /**
     * @var string
     */
    private $jobTag;

    /**
     * Constructor.
     *
     * @param string $registryService Service name of the definition registry in processed container
     * @param string $jobTag          The tag name used for jobs
     */
    public function __construct($registryService = 'abc.job.registry', $jobTag = 'abc.job')
    {
        $this->registryService = $registryService;
        $this->jobTag          = $jobTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->registryService) && !$container->hasAlias($this->registryService)) {
            return;
        }

        $customLogLevels = $container->getParameter('abc.job.logging.level');
        $registry        = $container->findDefinition('abc.job.registry');

        foreach ($container->findTaggedServiceIds($this->jobTag) as $id => $tags) {
            $def = $container->getDefinition($id);
            if (!$def->isPublic()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" must be public as jobs are lazy-loaded.', $id));
            }

            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "type" attribute on "%s" tags.', $id, $this->jobTag));
                }

                if (!isset($tag['method'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "method" attribute on "%s" tags.', $id, $this->jobTag));
                }

                $logLevel  = isset($customLogLevels[$tag['type']]) ? $this->levelToMonologConst($customLogLevels[$tag['type']]) : null;
                $jobTypeId = 'abc.job.type.' . $tag['type'];

                $definition = $this->createType(
                    $id,
                    $tag['type'],
                    array(new Reference($id), $tag['method']),
                    $logLevel
                );

                $container->setDefinition($jobTypeId, $definition);

                $registry->addMethodCall('register', array(new Reference($jobTypeId), true));
            }
        }

        // there as a reason this listener was registered here, what was it?
        if ($container->hasParameter('abc.job.adapter') && $container->getParameter('abc.job.adapter') == 'sonata') {
            $pass = new RegisterSonataListenersPass();
            $pass->process($container);
        }
    }

    /**
     * @param string           $serviceId
     * @param string           $type
     * @param callable         $callable
     * @param string|null      $logLevel
     * @param string|null      $formType
     * @return DefinitionDecorator
     */
    protected function createType($serviceId, $type, $callable, $logLevel = null, $formType = null)
    {
        $jobType = new DefinitionDecorator('abc.job.type.prototype');
        $jobType->replaceArgument(0, $serviceId);
        $jobType->replaceArgument(1, $type);
        $jobType->replaceArgument(2, $callable);
        $jobType->replaceArgument(3, $logLevel);

        return $jobType;
    }

    /**
     * @param $level
     * @return int|mixed
     */
    private function levelToMonologConst($level)
    {
        return is_int($level) ? $level : constant(Logger::class . '::' . strtoupper($level));
    }
}