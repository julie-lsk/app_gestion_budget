<?php
namespace App\Controller;

use App\Entity\MoyenDePaiement;
use App\Form\MoyenDePaiementType;
use App\Repository\MoyenDePaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/moyens-de-paiement')]
class MoyenDePaiementController extends AbstractController
{
    #[Route('/', name: 'moyen_index', methods: ['GET'])]
    public function index(MoyenDePaiementRepository $repo): Response
    {
        $moyens = $repo->findBy(['user' => $this->getUser()]);
        return $this->render('moyen_de_paiement/index.html.twig', [
            'moyens' => $moyens,
        ]);
    }

    #[Route('/nouveau', name: 'moyen_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $moyen = new MoyenDePaiement();
        $moyen->setUser($this->getUser());

        $form = $this->createForm(MoyenDePaiementType::class, $moyen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($moyen);
            $em->flush();
            $this->addFlash('success', 'Moyen ajouté !');
            return $this->redirectToRoute('moyen_index');
        }

        return $this->render('moyen_de_paiement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'moyen_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoyenDePaiement $moyen, EntityManagerInterface $em): Response
    {
        if ($moyen->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MoyenDePaiementType::class, $moyen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Moyen mis à jour !');
            return $this->redirectToRoute('moyen_index');
        }

        return $this->render('moyen_de_paiement/edit.html.twig', [
            'form'  => $form->createView(),
            'moyen' => $moyen,
        ]);
    }

    #[Route('/delete/{id}', name: 'moyen_delete', methods: ['POST'])]
    public function delete(Request $request, MoyenDePaiement $moyen, EntityManagerInterface $em): Response
    {
        if ($moyen->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $moyen->getId(), $request->request->get('_token'))) {
            $em->remove($moyen);
            $em->flush();
            $this->addFlash('warning', 'Moyen supprimé !');
        }

        return $this->redirectToRoute('moyen_index');
    }
}
