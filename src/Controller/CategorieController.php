<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'categorie_index')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $user = $this->getUser();
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findBy(['user' => $user]),
        ]);
    }

    #[Route('/new', name: 'categorie_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();
        $categorie->setUser($this->getUser());
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('categorie_index');
        }

        return $this->render('categorie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'categorie_edit')]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('categorie_index');
        }

        return $this->render('categorie/edit.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/delete', name: 'categorie_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        Categorie $categorie,
        CategorieRepository $repo
    ): Response {
        if ($categorie->getUser() !== $this->getUser()) {
            return $this->json(['success' => false, 'message' => "Accès refusé."], 403);
        }

        $data = json_decode($request->getContent(), true);
        $token = $data['_token'] ?? null;

        if (!$this->isCsrfTokenValid('delete-categorie-' . $categorie->getId(), $token)) {
            return $this->json(['success' => false, 'message' => "Jeton CSRF invalide."], 400);
        }

        $em->remove($categorie);
        $em->flush();

        $categories = $repo->findBy(['user' => $this->getUser()]);
        $listHtml = $this->renderView('dashboard/content/_categorie_list.html.twig', [
            'categories' => $categories,
        ]);

        return $this->json([
            'success' => true,
            'message' => "Catégorie supprimée avec succès !",
            'listHtml' => $listHtml,
        ]);
    }
}
