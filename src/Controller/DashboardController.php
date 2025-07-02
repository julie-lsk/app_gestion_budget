<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionNewForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard/{section}', name: 'app_dashboard', defaults: ['section' => 'dashboard'])]
    #[IsGranted('ROLE_USER')]
    public function index(string $section, Request $request, EntityManagerInterface $em): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionNewForm::class, $transaction, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setUser($this->getUser());
            $em->persist($transaction);
            $em->flush();

            $this->addFlash('success', 'Transaction ajoutée avec succès !');
            return $this->redirectToRoute('app_dashboard', ['section' => 'ajouter_transaction']);
        }

        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(),
            'section' => $section,
        ]);
    }
}
