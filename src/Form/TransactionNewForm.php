<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\MoyenDePaiement;
use App\Entity\Transaction;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
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
        $user = $options['user'];

        $builder
            ->add('montant', MoneyType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => TypeTransaction::cases(),
                'choice_label' => fn(TypeTransaction $choice) => ucfirst($choice->value),
                'placeholder' => 'Choisissez un type',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('date', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('categorie', EntityType::class, [
                'required' => true,
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez une catégorie',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (\App\Repository\CategorieRepository $repo) use ($user) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('c.nom', 'ASC');
                },
            ])
            ->add('moyenDePaiement', EntityType::class, [
                'required' => true,
                'class' => MoyenDePaiement::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un moyen de paiement',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (\App\Repository\MoyenDePaiementRepository $repo) use ($user) {
                    return $repo->createQueryBuilder('m')
                        ->where('m.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('m.nom', 'ASC');

                },
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
        ]);
    }
}

