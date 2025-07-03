<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/note')]
class NoteController extends AbstractController
{
    #[Route('/', name: 'note_index')]
    public function index(NoteRepository $noteRepository): Response
    {
        $user = $this->getUser();
        return $this->render('note/index.html.twig', [
            'notes' => $noteRepository->findBy(['user' => $user]),
        ]);
    }

    #[Route('/new', name: 'note_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $note = new Note();
        $note->setUser($this->getUser());
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();
            return $this->redirectToRoute('note_index');
        }

        return $this->render('note/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'note_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        Note $note,
        NoteRepository $repo
    ): Response {
        if ($note->getUser() !== $this->getUser()) {
            return $this->json(['success' => false, 'message' => "Accès refusé."], 403);
        }

        $data = json_decode($request->getContent(), true);
        $token = $data['_token'] ?? null;

        if (!$this->isCsrfTokenValid('delete-note-' . $note->getId(), $token)) {
            return $this->json(['success' => false, 'message' => "Jeton CSRF invalide."], 400);
        }

        $em->remove($note);
        $em->flush();

        $notes = $repo->findBy(['user' => $this->getUser()]);
        $listHtml = $this->renderView('dashboard/content/_note_list.html.twig', [
            'notes' => $notes,
        ]);

        return $this->json([
            'success' => true,
            'message' => "Note supprimée avec succès !",
            'listHtml' => $listHtml,
        ]);
    }
}
