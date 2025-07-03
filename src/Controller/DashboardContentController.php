<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Entity\Transaction;
use App\Form\TransactionNewForm;
use App\Repository\CategorieRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardContentController extends AbstractController
{
    // "page" est déterminée selon le js
    #[Route('/dashboard/content/{page}', name: 'dashboard_content')]
    public function loadContent(
        string                 $page,
        Request                $request,
        EntityManagerInterface $entityManager,
        CategorieRepository    $categorieRepository
    ): Response
    {
        switch ($page) {
            case 'dashboard':
                // Renvoie le template sélectionné
                return $this->render('dashboard/content/dashboard.html.twig');

            case 'recap':
                $user = $this->getUser();

                if (!$user) {
                    throw $this->createAccessDeniedException('Utilisateur non connecté');
                }
                $transactions = $entityManager->getRepository(Transaction::class)
                    ->findBy(['user' => $user], ['date' => 'DESC']);

                return $this->render('dashboard/content/recap.html.twig', [
                    'transactions' => $transactions,
                ]);


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
                    'user' => $this->getUser(),
                ]);

                // On l'affiche sur le template twig
                return $this->render('dashboard/content/ajouter_transaction.html.twig', [
                    'form' => $form->createView(),
                ]);

            case 'note':
                $user = $this->getUser();
                $notes = $entityManager
                    ->getRepository(Note::class)
                    ->findBy(['user' => $user]) ?: [];

                return $this->render('dashboard/content/note.html.twig', [
                    'notes' => $notes,
                ]);

            default:
                throw $this->createNotFoundException("Page '{$page}' non reconnue.");
        }
    }

    #[Route('/dashboard/content/note/new', name: 'dashboard_content_note_new', methods: ['GET', 'POST'])]
    public function newNoteModal(Request $request, EntityManagerInterface $em): Response
    {
        $note = new Note();
        $note->setUser($this->getUser());
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();

            $this->addFlash('success', 'Note ajoutée avec succès !');
            return $this->redirectToRoute('app_dashboard', ['page' => 'note']);
        }
        return $this->render('dashboard/content/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/content/note/edit/{id}', name: 'dashboard_content_note_edit', methods: ['GET', 'POST'])]
    public function editNoteModal(Request $request, EntityManagerInterface $em, Note $note): Response
    {
        if ($note->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Note modifiée avec succès !');
            return $this->redirectToRoute('app_dashboard', ['page' => 'note']);
        }

        return $this->render('dashboard/content/edit_note.html.twig', [
            'form' => $form->createView(),
            'note' => $note,
        ]);
    }


    #[Route('/dashboard/content/note/delete/{id}', name: 'dashboard_content_note_delete', methods: ['POST'])]
    public function deleteNote(Request $request, EntityManagerInterface $em, Note $note): Response
    {
        if ($note->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_dashboard', ['page' => 'note']);
        }

        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete' . $note->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_dashboard', ['page' => 'note']);
        }

        $em->remove($note);
        $em->flush();
        $this->addFlash('success', 'Note supprimée avec succès !');

        return $this->redirectToRoute('app_dashboard', ['page' => 'note']);
    }

    #[Route('/dashboard/content/categorie/new', name: 'dashboard_content_categorie_new', methods: ['GET', 'POST'])]
    public function newCategorieModal(
        Request                $request,
        EntityManagerInterface $em,
        CategorieRepository    $repo
    ): Response
    {
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
        Request                $request,
        EntityManagerInterface $em,
        CategorieRepository    $repo,
        int                    $id
    ): Response
    {
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

    #[Route('/data', name: 'dashboard_data', methods: ['GET'])]
    public function fetchGraphData(EntityManagerInterface $em): Response

    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        $conn = $em->getConnection();

        // Requête pour revenus par mois
        $revenus = $conn->executeQuery("
        SELECT MONTH(t.date) AS mois, SUM(t.montant) AS total
        FROM transaction t
        WHERE t.type = 'revenu' AND t.user_id = :userId
        GROUP BY mois
    ", ['userId' => $user->getId()])->fetchAllAssociative();

        // Requête pour dépenses par mois
        $depenses = $conn->executeQuery("
        SELECT MONTH(t.date) AS mois, SUM(t.montant) AS total
        FROM transaction t
        WHERE t.type = 'depense' AND t.user_id = :userId
        GROUP BY mois
    ", ['userId' => $user->getId()])->fetchAllAssociative();

        // Requête pour dépenses par catégorie
        $depensesParCategorie = $conn->executeQuery("
        SELECT c.nom AS categorie, SUM(t.montant) AS total
        FROM transaction t
        JOIN categorie c ON t.categorie_id = c.id
        WHERE t.type = 'depense' AND t.user_id = :userId
        GROUP BY categorie
    ", ['userId' => $user->getId()])->fetchAllAssociative();

        // Requête pour revenus par catégorie
        $revenusParCategorie = $conn->executeQuery("
        SELECT c.nom AS categorie, SUM(t.montant) AS total
        FROM transaction t
        JOIN categorie c ON t.categorie_id = c.id
        WHERE t.type = 'revenu' AND t.user_id = :userId
        GROUP BY categorie
    ", ['userId' => $user->getId()])->fetchAllAssociative();

// Requête pour les 5 dernières transactions
        $dernieresTransactions = $conn->executeQuery("
    SELECT t.date, t.montant, t.type
    FROM transaction t
    WHERE t.user_id = :userId
    ORDER BY t.date DESC
    LIMIT 5
", ['userId' => $user->getId()])->fetchAllAssociative();


        return $this->json([
            'revenusParMois' => $revenus,
            'depensesParMois' => $depenses,
            'depensesParCategorie' => $depensesParCategorie,
            'revenusParCategorie' => $revenusParCategorie,
            'dernieresTransactions' => $dernieresTransactions,
        ]);
    }
}
