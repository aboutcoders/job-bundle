<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Fixtures\Job\ProcessControl;

use Abc\ProcessControl\ControllerInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class DoExitController implements ControllerInterface
{
    /**
     * @return true
     */
    public function doExit()
    {
        return true;
    }
}