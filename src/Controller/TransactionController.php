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
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionNewForm::class, $transaction);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setUser($this->getUser());
            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Transaction ajoutée avec succès !');
            return $this->redirectToRoute('app_transaction');
        }

        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
