<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MockFactory
{
    /** @var \PHPUnit_Framework_MockObject_Generator */
    protected static $mockGenerator;

    public static function getMockGenerator()
    {
        if(is_null(static::$mockGenerator))
        {
            static::$mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
        }

        return static::$mockGenerator;
    }

    public static function getMock($type)
    {
        return static::getMockGenerator()->getMock($type);
    }
}