<?php
/*
* This file is part of the wcm-backend package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Form\Type;

use Abc\Bundle\JobBundle\Form\Transformer\ParamArrayToFormDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SecondsType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('seconds', $this->methodBlockPrefixExists() ? IntegerType::class : 'integer', [
            'constraints' => new Assert\GreaterThanOrEqual(['value' => 0])
        ]);

        $transformer = new ParamArrayToFormDataTransformer;
        $transformer->mapField('seconds', 0);

        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => null
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'abc_job_seconds';
    }

    /**
     * @return bool
     */
    private function methodBlockPrefixExists()
    {
        return method_exists(AbstractType::class, 'getBlockPrefix');
    }
}