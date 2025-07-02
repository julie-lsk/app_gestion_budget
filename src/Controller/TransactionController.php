<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\TransactionNewForm;
use App\Entity\Transaction;

// Ajoute le formulaire + traite les transactions
final class TransactionController extends AbstractController
{
    #[Route('/transaction', name: 'app_transaction')]
    #[IsGranted('ROLE_USER')] /* ce n'est qu'un user connecté qui peut créer */
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Nouvelle transaction
        $transaction = new Transaction();

        // Création du formulaire associé
        $form = $this->createForm(TransactionNewForm::class, $transaction);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Enregistrement en BDD selon l'utilisateur connecté
            $transaction->setUser($this->getUser());
            $entityManager->persist($transaction);
            $entityManager->flush();

            // Réinitialise le form + message de succès
            $transaction = new Transaction();
            $this->addFlash('success', 'Transaction ajoutée avec succès !');
            $form = $this->createForm(TransactionNewForm::class, $transaction);
        }

        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
