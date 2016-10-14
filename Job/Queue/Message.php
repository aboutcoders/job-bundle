<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Queue;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $ticket;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param string     $type
     * @param string     $ticket
     * @param array|null $parameters
     */
    function __construct($type, $ticket = null, array $parameters = null)
    {
        $this->ticket     = $ticket;
        $this->type       = $type;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}