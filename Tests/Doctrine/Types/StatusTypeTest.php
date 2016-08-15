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
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StatusTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $platform;

    public function setUp()
    {
        if (Type::hasType(StatusType::NAME)) {
            Type::overrideType(StatusType::NAME, StatusType::class);
        } else {
            Type::addType(StatusType::NAME, StatusType::class);
        }

        $this->platform = $this->getMockForAbstractClass(
            AbstractPlatform::class,
            [],
            "",
            true,
            true,
            true,
            [
                'getVarcharTypeDeclarationSQL'
            ]
        );
    }

    public function testGetName()
    {
        $this->assertEquals(StatusType::NAME, Type::getType(StatusType::NAME)->getName());
    }

    /**
     * @param mixed $value
     * @param int   $expectedResult
     * @throws \Doctrine\DBAL\DBALException
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValue($value, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            Type::getType(StatusType::NAME)->convertToDatabaseValue($value, $this->platform)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConvertToDatabaseValueThrowsInvalidArgumentException()
    {
        Type::getType(StatusType::NAME)->convertToDatabaseValue('foobar', $this->platform);
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
            ->method('getVarcharTypeDeclarationSQL')
            ->with(array_merge($fieldDeclaration, [
                'length' => 25
            ]))
            ->willReturn('foobar');

        $this->assertEquals('foobar', Type::getType(StatusType::NAME)->getSQLDeclaration($fieldDeclaration, $this->platform));
    }


    public function testRequiresSQLCommentHint()
    {
        $this->assertTrue(Type::getType(StatusType::NAME)->requiresSQLCommentHint($this->platform));
    }

    public static function provideConvertToDatabaseValues()
    {
        return [
            [Status::REQUESTED(), Status::REQUESTED()->getValue()],
            [Status::PROCESSING(), Status::PROCESSING()->getValue()],
            [Status::PROCESSED(), Status::PROCESSED()->getValue()],
            [Status::ERROR(), Status::ERROR()->getValue()],
            [Status::CANCELLED(), Status::CANCELLED()->getValue()],
            [Status::SLEEPING(), Status::SLEEPING()->getValue()],
            ['REQUESTED', Status::REQUESTED()->getValue()],
            ['PROCESSING', Status::PROCESSING()->getValue()],
            ['PROCESSED', Status::PROCESSED()->getValue()],
            ['ERROR', Status::ERROR()->getValue()],
            ['CANCELLED', Status::CANCELLED()->getValue()],
            ['SLEEPING', Status::SLEEPING()->getValue()]
        ];
    }
}