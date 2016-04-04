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

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Annotation\JobResponse;
use Abc\Bundle\JobBundle\Job\Metadata\ClassMetadata;
use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AnnotationDriver implements DriverInterface
{
    private $reader;

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

        $propertiesMetadata    = array();
        $propertiesAnnotations = array();

        foreach($class->getMethods() as $method)
        {
            /** @var \ReflectionMethod $method */

            if($method->class !== $name)
            {
                continue;
            }

            $methodAnnotations = $this->reader->getMethodAnnotations($method);

            foreach($methodAnnotations as $annot)
            {
                if($annot instanceof JobParameters)
                {
                    $classMetadata->setMethodArgumentTypes($method->getName(), $annot->typeList);
                }

                if($annot instanceof JobResponse)
                {
                    $classMetadata->setMethodReturnType($method->getName(), $annot->type);
                }
            }
        }

        return $classMetadata;
    }
}