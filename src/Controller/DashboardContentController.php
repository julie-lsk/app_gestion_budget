<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionNewForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


// Charge les différentes pages du dashboard
// Prépare la vue du formulaire --> ne traîte pas les données (traîtées par TransactionController)
final class DashboardContentController extends AbstractController
{
    // "page" est déterminée selon le js
    #[Route('/dashboard/content/{page}', name: 'dashboard_content')]
    public function loadContent(string $page, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Affichage des pages en fonction de la séléction dans le menu
        switch ($page) {
            case 'dashboard':
                // Renvoie le template sélectionné
                return $this->render('dashboard/content/dashboard.html.twig');

            case 'recap':
                // Renvoie le template sélectionné
                return $this->render('dashboard/content/recap.html.twig');

            case 'ajouter_transaction':
                // On ajoute une nouvelle transaction
                $transaction = new Transaction();

                // On associe la transaction à l'utilisateur
                $transaction->setUser($this->getUser());

                // Création du formulaire
                $form = $this->createForm(TransactionNewForm::class, $transaction);

                // On l'affiche sur le template twig
                return $this->render('dashboard/content/ajouter_transaction.html.twig', [
                    'form' => $form->createView(),
                ]);

            default:
                throw $this->createNotFoundException("Page '{$page}' non reconnue.");
        }
    }
}
