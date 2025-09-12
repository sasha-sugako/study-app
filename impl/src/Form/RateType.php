<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('rate', ChoiceType::class, [
            'required' => true,
            'label' => 'Hodnoceni kolekci:',
            'choices' => [
                '5' => 5,
                '4' => 4,
                '3' => 3,
                '2' => 2,
                '1' => 1],
            'expanded' => true,
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Zanechat recenzi',
            'required' => false,
            'attr' => [
               'maxlength' => 255,
                ],
        ]
    );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class
        ]);
    }
}