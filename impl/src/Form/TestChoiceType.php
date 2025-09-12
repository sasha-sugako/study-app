<?php

namespace App\Form;

use App\Entity\Deck;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('types_of_questions', ChoiceType::class, [
            'label' => 'Vyberte typy otázek',
            'choices' => [
                'Výběr z možností' => 1,
                'Pravda nebo lež' => 2,
                'Úplné doplnění' => 3
            ],
            'multiple' => true,
            'expanded' => true,
            'required' => true,
        ])->add('number_of_questions', ChoiceType::class, [
            'label' => 'Vyberte počet otázek',
            'choices' => [
                5 => 5,
                7 => 7,
                10 => 10
            ],
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}