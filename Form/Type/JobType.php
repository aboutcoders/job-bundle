<?php
/*
* This file is part of the setmeup-backend package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Form\Type;

use Abc\Bundle\JobBundle\Job\JobTypeRegistry;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 *
 * Note:
 * - In order to apply validation constraints that are defined with annotations for the data cass it necessary to set the entity class in $options['data']
 * - Somehow violations of parameter forms are not mapped properly if constraints are not defined within the form but the data class (e.g. annotation)
 *
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobType extends AbstractType
{
    /**
     * @var JobTypeRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    private $dataClass;

    /**
     * @param JobTypeRegistry $registry
     * @param string $dataClass
     */
    public function __construct(JobTypeRegistry $registry, $dataClass = 'Abc\Bundle\JobBundle\Entity\Job')
    {
        $this->registry = $registry;
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $registry = $this->registry;

        $builder->add('type', TextType::class);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($registry)
            {

                /** @var JobInterface $job */
                $job = $event->getData();
                /** @var Form $form */
                $form = $event->getForm();

                if(!is_null($job) && !is_null($job->getType()) && $registry->has($job->getType()))
                {
                    $formClass = $registry->get($job->getType())->getFormClass();
                    if(!is_null($formClass))
                    {
                        $form->add('parameters', MessageType::class, [
                            'constraints' => [new Valid()]
                        ]);
                    }
                }
            }
        );

        $builder->add('schedules', CollectionType::class, [
                'by_reference' => false,
                'entry_type' => ScheduleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
                'constraints' => [new Valid()]
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'csrf_protection' => false,

            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}