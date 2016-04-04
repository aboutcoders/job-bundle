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
 * ContextInterface
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface ContextInterface
{

    /**
     * Gets the context parameters.
     *
     * @return array An array of parameters
     */
    public function all();

    /**
     * Clears all parameters.
     *
     * @return void
     */
    public function clear();

    /**
     * Gets a context parameter.
     *
     * @param string $name The parameter name
     * @return mixed  The parameter value
     * @throws ParameterNotFoundException if the parameter is not defined
     */
    public function get($name);

    /**
     * Sets a context parameter.
     *
     * @param string $name The parameter name
     * @param mixed  $value The parameter value
     * @return void
     */
    public function set($name, $value);

    /**
     * Returns true if a parameter name is defined.
     *
     * @param string $name The parameter name
     * @return bool Returns true if the parameter name is defined otherwise false
     */
    public function has($name);

    /**
     * @param string $name The parameter name
     * @return void
     */
    public function remove($name);
}