<?php

namespace App\Form;

use App\Entity\Card;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $card = $options['data'];
        $builder
            ->add('front_image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg, image/png, image/gif, image/jpg'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'],
                        'mimeTypesMessage' => 'Nahrajte obrázek (JPEG, JPG, PNG, GIF)',
                    ])
                ]
            ])
            ->add('front_side', TextareaType::class, [
                'label' => 'Přední strana',
                'required' => true,
            ])
            ->add('back_image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg, image/png, image/gif, image/jpg'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'],
                        'mimeTypesMessage' => 'Nahrajte obrázek (JPEG, JPG, PNG, GIF)',
                    ])
                ]
            ])
            ->add('back_side',TextareaType::class, [
                'label' => 'Zadní strana',
                'required' => true,
            ]);
        if ($card && $card->getFrontImage()){
            $builder->add('remove_front_image', CheckboxType::class, [
                'label' => 'Smazat aktuální obrázek?',
                'required' => false,
                'mapped' => false,
            ]);
        }
        if ($card && $card->getBackImage()){
            $builder->add('remove_back_image', CheckboxType::class, [
                'label' => 'Smazat aktuální obrázek?',
                'required' => false,
                'mapped' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Card::class,
        ]);
    }
}
