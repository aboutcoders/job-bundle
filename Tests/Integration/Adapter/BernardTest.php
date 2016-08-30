<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Integration\Adapter;

use Abc\Bundle\JobBundle\Test\AdapterTest;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class BernardTest extends AdapterTest
{
    public function getEnvironment()
    {
        return 'bernard';
    }
}