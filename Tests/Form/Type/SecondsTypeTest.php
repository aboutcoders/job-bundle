<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Form\Type;

use Abc\Bundle\JobBundle\Form\Type\SecondsType;
use Symfony\Component\Form\AbstractType;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SecondsTypeTest extends TypeTestCase
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'sleeper';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return method_exists(AbstractType::class, 'getBlockPrefix') ? SecondsType::class : 'abc_job_seconds';
    }

    /**
     * {@inheritdoc}
     */
    public function provideTestData()
    {
        return [
            [
                [
                    'seconds' => 5
                ],
                [5]
            ]
        ];
    }
}