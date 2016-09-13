<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Functional\Validator;

use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Abc\Bundle\JobBundle\Model\Job;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobValidationTest extends KernelTestCase
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        static::bootKernel();

        $this->validator = static::$kernel->getContainer()->get('validator');
    }

    public function testAppliesConstraintsOfObjects()
    {
        $job = new Job();
        $job->setType('abc.mailer');
        $job->setParameters([new Message('asd', 'from@domain.tld', 'Subject', 'Bessage Body')]);

        $errors = $this->validator->validate($job);

        $this->assertTrue($errors->has(0));
        $this->assertEquals('parameters[0].to', $errors->get(0)->getPropertyPath());
        $this->assertEquals('This value is not a valid email address.', $errors->get(0)->getMessage());
    }

    public function testAppliesConstraintsOfConstraintProviders()
    {
        $job = new Job();
        $job->setType('abc.sleeper');
        $job->setParameters(['aasd']);

        $errors = $this->validator->validate($job);
        $this->assertTrue($errors->has(0));
        $this->assertEquals('parameters[0]', $errors->get(0)->getPropertyPath());
        $this->assertEquals('This value should be a valid number.', $errors->get(0)->getMessage());
    }
}