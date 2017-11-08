<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class ModalItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'raw' => 'RAW_TYPE',
                    'image' => 'IMAGE_TYPE',
                    'video' => 'VIDEO_TYPE',
                    'paragraph' => 'PARAGRAPH_TYPE',
                    'link' => 'LINK_TYPE'
                ],
                'attr' => [
                    'class' => 'hidden'
                ]
            ])
            ->add('body', TextareaType::class, [
                'label' => false
            ])
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('attachment', FileType::class, [
                'label' => null,
                'data_class' => null,
                'required' => false
            ])
        ->add('portfolio', null, [
            'label' => false,
            'attr' => [
                'class' => 'hidden'
            ]
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ModalItem'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_modalitem';
    }


}
