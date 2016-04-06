<?php
/*
* This file is part of the job-bundle-annotations package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Tests\Form\Type;

use Abc\Bundle\JobBundle\Form\Type\ScheduleType;
use Abc\Bundle\JobBundle\Entity\Schedule;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Tests\Extension\Validator\Type\TypeTestCase as ValidatorTypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ScheduleTypeTest extends ValidatorTypeTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
    }

    /**
     * @param $formData
     * @param $object
     * @dataProvider provideValidData
     */
    public function testSubmitValidData($formData, $object)
    {
        $form = $this->factory->create(method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? ScheduleType::class : 'abc_job_schedule', new Schedule());

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach(array_keys($formData) as $key)
        {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function provideValidData()
    {
        return [
            [['type' => 'cron', 'expression' => '* * * * *'], new Schedule('cron', '* * * * *')]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        if(method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix'))
        {
            return parent::getExtensions();
        }
        else
        {
            $form = new ScheduleType();

            return array_merge(parent::getExtensions(), array(
                new PreloadedExtension([
                    $form->getName() => $form
                ], [])));
        }
    }
}