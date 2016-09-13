<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Job\Parameter;

use Abc\Bundle\JobBundle\Validator\Job\AbstractConstraintProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class DefaultJobsConstraintProvider extends AbstractConstraintProvider
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints($type)
    {
        switch ($type) {
            case 'abc.mailer':
                return $this->provideMailerConstraints();
                break;
            case 'abc.sleeper':
                return $this->provideSleeperConstraints();
                break;
        }

        return null;
    }

    /**
     * @return array
     */
    protected function provideMailerConstraints()
    {
        return [new Assert\NotBlank()];
    }

    /**
     * @return array
     */
    protected function provideSleeperConstraints()
    {
        return [new Assert\Range(['min' => 1])];
    }
}