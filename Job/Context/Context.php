<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Context;

use Abc\Bundle\JobBundle\Job\Context\Exception\ParameterNotFoundException;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Context implements ContextInterface
{

    protected $parameters = array();

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = array())
    {
        foreach($parameters as $name => $parameter)
        {
            $this->set($name, $parameter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->parameters = array();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $name = strtolower($name);
        if(!array_key_exists($name, $this->parameters))
        {
            throw new ParameterNotFoundException($name);
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->parameters[strtolower($name)] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists(strtolower($name), $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        unset($this->parameters[strtolower($name)]);
    }
}