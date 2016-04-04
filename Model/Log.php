<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Model;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class Log implements LogInterface
{
    /**
     * @var string
     */
    protected $channel;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var string
     */
    protected $levelName;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \DateTime
     */
    protected $datetime;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var array
     */
    protected $extra;


    public function __construct()
    {
        $this->context = [];
        $this->extra = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevelName($levelName)
    {
        $this->levelName = $levelName;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * {@inheritdoc}
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * {@inheritdoc}
     */
    public function toRecord()
    {
        return [
            'channel' => $this->getChannel(),
            'level' => $this->getLevel(),
            'level_name' => $this->getLevelName(),
            'message' => $this->getMessage(),
            'datetime' => $this->getDatetime(),
            'context' => $this->getContext(),
            'extra' => $this->getExtra(),
        ];
    }
}