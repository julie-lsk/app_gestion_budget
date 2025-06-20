<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\MoyenDePaiement;
use App\Entity\Transaction;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\TypeTransaction;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionNewForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Source : https://symfony.com/doc/current/reference/forms/types.html

        $builder
            ->add('montant', MoneyType::class, [
                'required' => 'true',
            ])
            ->add('type', ChoiceType::class, [
                'required' => 'true',
                'choices' => TypeTransaction::cases(), /* renvoi les 2 options revenu/dépense (de enum) */
                'choice_label' => fn(TypeTransaction $choice) => ucfirst($choice->value),
                'placeholder' => 'Choisissez un type',
            ])
            ->add('date', DateType::class, [
                'required' => 'true',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez une catégorie',
            ])
            ->add('moyenDePaiement', EntityType::class, [
                'required' => 'true',
                'class' => MoyenDePaiement::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un moyen de paiement',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
