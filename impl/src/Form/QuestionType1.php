<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType1 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('answer', ChoiceType::class, [
                'choices' => $options['answers'],
                'choice_label' => function ($choice) {
                   return $choice;
                },
                    'choice_value' => function ($choice) {
                        return preg_replace('/\s*\n\s*/', '\n', trim($choice));
                    },
                'label' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'placeholder' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'answers' => []
        ]);
    }
}