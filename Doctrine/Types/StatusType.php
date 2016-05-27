<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Doctrine\Types;

use Abc\Bundle\JobBundle\Job\Status;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Doctrine type mapping for \Abc\Bundle\JobBundle\Job\Status
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class StatusType extends Type
{
    const NAME = 'abc.job.status';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value == null ? null : new Status((string)$value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Status) {
            return $value->getValue();
        } elseif (is_string($value)) {
            try {
                return call_user_func(Status::class.'::'.$value)->getValue();
            } catch(\BadMethodCallException $e) {
                throw new \InvalidArgumentException(sprintf('Could not convert given value to database value (value must instance of %s or a string matching the constant name)', Status::class));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if(!isset($fieldDeclaration['length']))
        {
            $fieldDeclaration['length'] = 25;
        }

        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}