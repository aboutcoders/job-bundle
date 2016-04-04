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

use Abc\Bundle\JobBundle\Job\LogManagerInterface as BaseLogManagerInterface;
use Monolog\Formatter\FormatterInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
interface LogManagerInterface extends BaseLogManagerInterface
{
    /**
     * @return string The fully qualified class name of the entity class.
     */
    public function getClass();

    /**
     * @return LogInterface
     */
    public function create();

    /**
     * @param LogInterface $log
     * @param bool      $andFlush Whether to flush the changes (default true)
     * @return void
     */
    public function save(LogInterface $log, $andFlush = true);

    /**
     * @return LogInterface[]
     */
    public function findAll();

    /**
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     * @throws \UnexpectedValueException
     * @return LogInterface[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param LogInterface $log
     * @return int The number of deleted entities
     */
    public function delete(LogInterface $log);

    /**
     * @param FormatterInterface $formatter
     * @return void
     */
    public function setFormatter(FormatterInterface $formatter);

    /**
     * @return FormatterInterface
     */
    public function getFormatter();
}