<?php
namespace App\Controller;

use App\Repository\NoteRepository;    // ← ajoute ceci
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

final class DashboardController extends AbstractController
{
#[Route('/dashboard', name: 'app_dashboard')]
#[IsGranted('ROLE_USER')]
public function index(Request $request, NoteRepository $noteRepo): Response
{
$currentPage = $request->query->get('page', 'dashboard');

// Par défaut pas de notes
$notes = [];

// Si on affiche l’onglet "Notes", on charge vraiment les notes
if ($currentPage === 'note') {
$notes = $noteRepo->findBy(['user' => $this->getUser()]) ?: [];
}

return $this->render('dashboard/index.html.twig', [
'user'        => $this->getUser(),
'currentPage' => $currentPage,
'notes'       => $notes,   // ← on transmet ici !
]);
}
}
