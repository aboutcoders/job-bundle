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

use Abc\ProcessControl\Controller;
use Abc\ProcessControl\ControllerAwareInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TestControllerAwareCallable implements ControllerAwareInterface
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function execute()
    {
        return 'foobar';
    }
}