<?php

namespace App\Form;

use App\Entity\AppUser;
use App\Entity\Deck;
use App\Repository\DeckRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceDeckType extends AbstractType
{

    public function __construct(
      private DeckRepository $deckRepository
    ){}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $options['currentUser'];
        $builder->add('deck_choice', EntityType::class, [
            'class' => Deck::class,
            'label' => 'Zvolte kolekci',
            'required' => true,
            'choices' => $currentUser ? $this->deckRepository->findByUser($currentUser): [],
            'choice_label' => 'name',
        ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'currentUser' => null,
        ]);
    }
}