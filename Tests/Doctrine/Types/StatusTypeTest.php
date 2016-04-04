<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Doctrine\Types;

use Abc\Bundle\JobBundle\Doctrine\Types\StatusType;
use Abc\Bundle\JobBundle\Job\Status;
use Doctrine\DBAL\Types\Type;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StatusTypeTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $platform;

    public function setUp()
    {
        if(Type::hasType(StatusType::NAME))
        {
            Type::overrideType(StatusType::NAME, 'Abc\Bundle\JobBundle\Doctrine\Types\StatusType');
        }
        else
        {
            Type::addType(StatusType::NAME, 'Abc\Bundle\JobBundle\Doctrine\Types\StatusType');
        }

        $this->platform = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');
    }

    public function testGetName()
    {
        $this->assertEquals(StatusType::NAME, Type::getType(StatusType::NAME)->getName());
    }

    public function testConvertToDatabaseValue()
    {
        $status = Status::PROCESSED();

        $this->assertEquals(
            $status->getValue(),
            Type::getType(StatusType::NAME)->convertToDatabaseValue($status, $this->platform)
        );
    }

    public function testConvertToPHPValue()
    {
        $status = Status::PROCESSED();

        $this->assertEquals(
            $status,
            Type::getType(StatusType::NAME)->convertToPHPValue($status->getValue(), $this->platform)
        );
    }

    public function testGetSQLDeclaration()
    {
        $fieldDeclaration = array('foo');

        $this->platform->expects($this->once())
            ->method('getSmallIntTypeDeclarationSQL')
            ->with($fieldDeclaration)
            ->willReturn('foobar');

        $this->assertEquals('foobar', Type::getType(StatusType::NAME)->getSQLDeclaration($fieldDeclaration, $this->platform));
    }


    public function testRequiresSQLCommentHint()
    {
        $this->assertTrue(Type::getType(StatusType::NAME)->requiresSQLCommentHint($this->platform));
    }
}