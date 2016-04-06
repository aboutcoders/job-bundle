<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Form\Type;

use Abc\Bundle\JobBundle\Form\Transformer\SingleParameterTransformer;
use Abc\Bundle\JobBundle\Job\Mailer\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class MessageType extends AbstractType
{
    private $dataClass;

    /**
     * @param string $dataClass
     */
    public function __construct($dataClass = 'Abc\Bundle\JobBundle\Job\Mailer\Message')
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!isset($options['data']))
        {
            $options['data'] = new Message();
        }

        $builder->add('to', 'email');
        $builder->add('from', $this->methodBlockPrefixExists() ? EmailType::class : 'email');
        $builder->add('subject', $this->methodBlockPrefixExists() ? TextType::class : 'text');
        $builder->add('message', $this->methodBlockPrefixExists() ? TextAreaType::class : 'textarea');

        // transform Abc\Bundle\JobBundle\Job\Mailer\Message to array for model
        $builder->addModelTransformer(new SingleParameterTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->dataClass
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'abc_job_message';
    }

    /**
     * @return bool
     */
    private function methodBlockPrefixExists()
    {
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
    }
}