<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionNewForm;
use App\Repository\CategorieRepository;
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
    public function loadContent(string $page, Request $request, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): Response
    {
        // Affichage des pages en fonction de la séléction dans le menu
        switch ($page) {
            case 'dashboard':
                // Renvoie le template sélectionné
                return $this->render('dashboard/content/dashboard.html.twig');

            case 'recap':
                // Renvoie le template sélectionné
                return $this->render('dashboard/content/recap.html.twig');

            case 'categorie':
                $user = $this->getUser();
                $categories = $categorieRepository->findBy(['user' => $user]);

                return $this->render('dashboard/content/categorie.html.twig', [
                    'categories' => $categories,
                ]);

            case 'ajouter_transaction':
                // On ajoute une nouvelle transaction
                $transaction = new Transaction();

                // On associe la transaction à l'utilisateur
                $transaction->setUser($this->getUser());

                // Création du formulaire
                $form = $this->createForm(TransactionNewForm::class, $transaction, [
                    'user' => $this->getUser(), // 👈 indispensable
                ]);

                // On l'affiche sur le template twig
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
        \App\Repository\CategorieRepository $repo // ← à ajouter ici si absent !
    ): Response
    {
        $categorie = new \App\Entity\Categorie();
        $categorie->setUser($this->getUser());
        $form = $this->createForm(\App\Form\CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            // Rendu du HTML de la liste à jour
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

        // Vérification que la catégorie appartient à l’utilisateur connecté
        if (!$categorie || $categorie->getUser() !== $this->getUser()) {
            return $this->json(['success' => false, 'message' => "Catégorie non trouvée."], 404);
        }

        $form = $this->createForm(\App\Form\CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Recharge la liste à jour (comme pour l’ajout)
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
