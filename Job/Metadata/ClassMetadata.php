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
    protected $parameterNames = array();

    /**
     * @var array
     */
    protected $parameterTypes = array();

    /**
     * @var array
     */
    protected $parameterOptions = array();

    /**
     * @var array
     */
    protected $returnTypes = array();

    /**
     * @var array
     */
    protected $returnOptions = array();

    /**
     * Returns whether a method has ben added.
     *
     * @param string $name
     * @return bool Whether a method has ben added
     */
    public function hasMethod($name)
    {
        return isset($this->parameterNames[$name]);
    }

    /**
     * @param string $method The method name
     * @param array  $names  The name of the parameters as defined in the method signature
     */
    public function addMethod($method, array $names)
    {
        $this->parameterNames[$method]   = $names;
        $this->parameterTypes[$method]   = array();
        $this->parameterOptions[$method] = array();

        foreach ($names as $name) {
            $this->parameterTypes[$method][$name]   = null;
            $this->parameterOptions[$method][$name] = array();
        }
    }

    /**
     * Sets the type of a parameter.
     *
     * @param string $method The name of the method
     * @param string $name   The name of the parameter
     * @param string $type   The type of the parameter
     * @throws \InvalidArgumentException If the method is not defined
     * @throws \InvalidArgumentException If the method is not defined
     */
    public function setParameterType($method, $name, $type)
    {
        if (!isset($this->parameterTypes[$method])) {
            throw new \InvalidArgumentException(sprintf('A method with name "%s" is not defined', $name, $method));
        }

        if (!array_key_exists($name, $this->parameterTypes[$method])) {
            throw new \InvalidArgumentException(sprintf('A parameter with name "%s" for method "%s" is not defined', $name, $method));
        }

        $this->parameterTypes[$method][$name] = $type;
    }

    /**
     * @param string $method The name of the method
     * @return array The types of the method parameters
     */
    public function getParameterTypes($method)
    {
        if (!isset($this->parameterTypes[$method])) {
            return array();
        }

        $types = array();
        foreach ($this->parameterTypes[$method] as $name => $type) {
            $types[] = $type;
        }

        return $types;
    }

    /**
     * @param string $method  The name of the method
     * @param string $name    The name of the parameter
     * @param array  $options An array of options
     * @throws \InvalidArgumentException If the method is not defined
     * @throws \InvalidArgumentException If the method is not defined
     */
    public function setParameterOptions($method, $name, array $options)
    {
        if (!isset($this->parameterTypes[$method])) {
            throw new \InvalidArgumentException(sprintf('A method with name "%s" is not defined', $method));
        }

        if (!array_key_exists($name, $this->parameterTypes[$method])) {
            throw new \InvalidArgumentException(sprintf('A parameter with name "%s" for method "%s" is not defined', $name, $method));
        }
        $this->parameterOptions[$method][$name] = $options;
    }

    /**
     * @param string $method The name of the method
     * @return array The types of the method parameters
     */
    public function getParameterOptions($method)
    {
        if (!isset($this->parameterOptions[$method])) {
            return array();
        }

        $result = array();
        foreach ($this->parameterOptions[$method] as $name => $options) {
            $result[] = $options;
        }

        return $result;
    }

    /**
     * @param string $method The name of the method
     * @param string $type   The return type
     * @return void
     */
    public function setReturnType($method, $type)
    {
        $this->returnTypes[$method] = $type;
    }

    /**
     * @param string $method The name of the method
     * @return string The return type
     */
    public function getReturnType($method)
    {
        return isset($this->returnTypes[$method]) ? $this->returnTypes[$method] : null;
    }

    /**
     * @param string $method The name of the method
     * @param  array $options
     * @return void
     */
    public function setReturnOptions($method, array $options)
    {
        $this->returnOptions[$method] = $options;
    }

    /**
     * @param string $method The name of the method
     * @return string The return type
     */
    public function getReturnOptions($method)
    {
        return isset($this->returnOptions[$method]) ? $this->returnOptions[$method] : array();
    }
}