<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimelineType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('logo', FileType::class, [
                'label' => false,
                'data_class' => null,
                'attr' => [
                    'class' => 'hidden'
                ]
            ])
            ->add('beginDate', DateType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'd-inline'
                ]
            ])
            ->add('endDate', DateType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'd-inline'
                ]
            ])
            ->add('employer', TextType::class, [
                'attr' => ['placeholder' => 'Werkgever'],
                'label' => false
            ])
            ->add('function', TextType::class, [
                'attr' => ['placeholder' => 'Functie'],
                'label' => false
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['placeholder' => 'Beschrijving'],
                'label' => false
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Timeline'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_timeline';
    }


}
