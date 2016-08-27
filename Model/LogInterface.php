<?php
/*
* This file is part of the job-bundle package.
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
interface LogInterface
{
    /**
     * @return string
     */
    public function getChannel();

    /**
     * @param string|null $channel
     * @return void
     */
    public function setChannel($channel);

    /**
     * @return int
     */
    public function getLevel();

    /**
     * @param int $level
     * @return void
     */
    public function setLevel($level);

    /**
     * @return string
     */
    public function getLevelName();

    /**
     * @param string $levelName
     * @return void
     */
    public function setLevelName($levelName);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string|null $message
     * @return void
     */
    public function setMessage($message);

    /**
     * @return \DateTime
     */
    public function getDatetime();

    /**
     * @param \DateTime $datetime
     * @return void
     */
    public function setDatetime($datetime);

    /**
     * @return array
     */
    public function getContext();

    /**
     * @param array|null $context
     * @return void
     */
    public function setContext($context);

    /**
     * @return array
     */
    public function getExtra();

    /**
     * @param array|null $extra
     * @return void
     */
    public function setExtra($extra);

    /**
     * @return array The PSR compliant log record
     */
    public function toRecord();
}