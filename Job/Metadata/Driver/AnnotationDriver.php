<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Metadata\Driver;

use Abc\Bundle\JobBundle\Annotation\ParamType;
use Abc\Bundle\JobBundle\Annotation\ReturnType;
use Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata;
use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return \Metadata\ClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata                  = new ClassMetadata($name = $class->name);
        $classMetadata->fileResources[] = $class->getFilename();
        foreach ($class->getMethods() as $method) {
            /**
             * @var \ReflectionMethod $method
             */
            if ($method->class !== $name) {
                continue;
            }

            $methodAnnotations = $this->reader->getMethodAnnotations($method);
            foreach ($methodAnnotations as $annotation) {
                if ($annotation instanceof ParamType) {
                    if(!$classMetadata->hasMethod($method->name)) {
                        $this->addMethod($classMetadata, $method);
                    }
                    $classMetadata->setParameterType($method->getName(), $annotation->name, $annotation->type);
                    $classMetadata->setParameterOptions($method->getName(), $annotation->name, $annotation->options);
                }

                if ($annotation instanceof ReturnType) {
                    $classMetadata->setReturnType($method->getName(), $annotation->type);
                }
            }
        }

        return $classMetadata;
    }

    /**
     * @param ClassMetadata     $classMetadata
     * @param \ReflectionMethod $method
     */
    protected function addMethod(ClassMetadata $classMetadata, \ReflectionMethod $method)
    {
        $names = array();
        foreach ($method->getParameters() as $parameter) {
            $names[] = $parameter->getName();
        }

        $classMetadata->addMethod($method->getName(), $names);
    }
}