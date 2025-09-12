<?php

namespace App\Form;

use App\Entity\AppUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label'=>'E-mail',
                'required' => false,
                'mapped' => false,
            ])
            ->add('login', TextType::class, [
                'label' => 'Login',
                'required' => false,
                'mapped' => false,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Heslo',
                'required' => false,
                'mapped' => false,
            ])
            ->add('new_verification', CheckboxType::class, [
                'label' => 'Znovu odeslat potvrzovací e-mail',
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
