<?php

namespace App\Form;

use App\Controller\Filter\DeckFilter;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DeckFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                    'label' => 'Nazev',
                    'required' => false,
                ]
            )
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'label' => 'Kategorii',
                'required' => false,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('min_rate', NumberType::class, [
                'label' => 'Minimální hodnocení',
                'attr' => ['min' => 1, 'max' => 5],
                'required' => false,
                'html5' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeckFilter::class,
        ]);
    }
}
