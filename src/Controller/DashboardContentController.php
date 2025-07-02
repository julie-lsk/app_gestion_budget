<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\Repository\CategorieRepository;
use App\Form\TransactionNewForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Charge les différentes pages du dashboard
final class DashboardContentController extends AbstractController
{
    // "page" est déterminée selon le JS
    #[Route('/dashboard/content/{page}', name: 'dashboard_content')]
    public function loadContent(
        string $page,
        Request $request,
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepository
    ): Response {
        switch ($page) {
            case 'dashboard':
                // DONNÉES DE TEST pour tous les graphiques (aucune variable manquante !)
                $revenusLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
                $revenusData = [1200, 1300, 1250, 1400, 1350, 1450];

                $depensesLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
                $depensesData = [900, 1100, 950, 1200, 1000, 1100];

                $depensesCategorieLabels = ['Loyer', 'Courses', 'Transport', 'Santé', 'Autres'];
                $depensesCategorieData = [700, 350, 150, 100, 200];

                $revenusCategorieLabels = ['Salaire', 'Allocations', 'Ventes', 'Autres'];
                $revenusCategorieData = [1200, 350, 250, 100];

                return $this->render('dashboard/content/dashboard.html.twig', [
                    'revenusLabels' => json_encode($revenusLabels),
                    'revenusData' => json_encode($revenusData),
                    'depensesLabels' => json_encode($depensesLabels),
                    'depensesData' => json_encode($depensesData),
                    'depensesCategorieLabels' => json_encode($depensesCategorieLabels),
                    'depensesCategorieData' => json_encode($depensesCategorieData),
                    'revenusCategorieLabels' => json_encode($revenusCategorieLabels),
                    'revenusCategorieData' => json_encode($revenusCategorieData),
                ]);

            case 'recap':
                return $this->render('dashboard/content/recap.html.twig');

            case 'categorie':
                $user = $this->getUser();
                $categories = $categorieRepository->findBy(['user' => $user]);

                return $this->render('dashboard/content/categorie.html.twig', [
                    'categories' => $categories,
                ]);

            case 'ajouter_transaction':
                $transaction = new Transaction();
                $transaction->setUser($this->getUser());
                $form = $this->createForm(TransactionNewForm::class, $transaction);

                return $this->render('dashboard/content/ajouter_transaction.html.twig', [
                    'form' => $form->createView(),
                ]);

            default:
                throw $this->createNotFoundException("Page '{$page}' non reconnue.");
        }
    }

    #[Route('/dashboard/content/categorie/new', name: 'dashboard_content_categorie_new', methods: ['GET', 'POST'])]
    public function newCategorieModal(
        Request $request,
        EntityManagerInterface $em,
        \App\Form\Repository\CategorieRepository $repo
    ): Response {
        $categorie = new \App\Entity\Categorie();
        $categorie->setUser($this->getUser());
        $form = $this->createForm(\App\Form\CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            $categories = $repo->findBy(['user' => $this->getUser()]);
            $listHtml = $this->renderView('dashboard/content/_categorie_list.html.twig', [
                'categories' => $categories,
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Catégorie enregistrée avec succès !',
                'listHtml' => $listHtml,
            ]);
        }

        return $this->render('dashboard/content/_categorie_new_modal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/content/categorie/edit/{id}', name: 'dashboard_content_categorie_edit', methods: ['GET', 'POST'])]
    public function editCategorieModal(
        Request $request,
        EntityManagerInterface $em,
        CategorieRepository $repo,
        int $id
    ): Response {
        $categorie = $repo->find($id);

        if (!$categorie || $categorie->getUser() !== $this->getUser()) {
            return $this->json(['success' => false, 'message' => "Catégorie non trouvée."], 404);
        }

        $form = $this->createForm(\App\Form\CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $categories = $repo->findBy(['user' => $this->getUser()]);
            $listHtml = $this->renderView('dashboard/content/_categorie_list.html.twig', [
                'categories' => $categories,
            ]);
            return $this->json([
                'success' => true,
                'message' => 'Catégorie modifiée avec succès !',
                'listHtml' => $listHtml,
            ]);
        }

        return $this->render('dashboard/content/_categorie_edit_modal.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }
}
