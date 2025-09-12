<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Deck;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DeckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                    'label' => 'Nazev',
                    'required' => true,
                ]
            )
            ->add('about', TextareaType::class, [
                    'label' => 'Popis',
                    'required' => false,
                ]
            )
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'label' => 'Přidat kategorii',
                'required' => false,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('new_category', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Přidat novou kategorii',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Deck::class,
        ]);
    }
}
