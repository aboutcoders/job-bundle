<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Fixtures\Job;

use Abc\Bundle\JobBundle\Job\ManagerAwareInterface;
use Abc\Bundle\JobBundle\Job\ManagerInterface;
use Psr\Log\LoggerInterface;
use Abc\Bundle\JobBundle\Annotation\ParamType;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ManagerAwareJob implements ManagerAwareInterface
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return string
     */
    public function execute()
    {
        return 'foobar';
    }
}