<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Metadata;

use \Metadata\ClassMetadata as BaseClassMetadata;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ClassMetadata extends BaseClassMetadata
{
    /**
     * @var array
     */
    protected $methodArgumentTypes = array();

    /**
     * @var array
     */
    protected $methodReturnTypes = array();

    /**
     * @param string $method The method name
     * @param array  $typeList A list of argument types
     */
    public function setMethodArgumentTypes($method, array $typeList = null)
    {
        $this->methodArgumentTypes[$method] = $typeList;
    }

    /**
     * @param string $method The method name
     * @return array|null A list of argument types
     */
    public function getMethodArgumentTypes($method)
    {
        return isset($this->methodArgumentTypes[$method]) ? $this->methodArgumentTypes[$method] : null;
    }

    /**
     * @param string      $method The method name
     * @param string|null $type The return type
     */
    public function setMethodReturnType($method, $type = null)
    {
        $this->methodReturnTypes[$method] = $type;
    }

    /**
     * @param string $method The method name
     * @return string|null The return type
     */
    public function getMethodReturnType($method)
    {
        return isset($this->methodReturnTypes[$method]) ? $this->methodReturnTypes[$method] : null;
    }
}