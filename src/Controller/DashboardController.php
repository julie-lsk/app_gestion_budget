<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    // Autorise l'accès à la route uniquement aux utilisateurs connectés
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            // Nous donnera accès aux infos du User (user.email par ex)
            'user' => $this->getUser(),
        ]);
    }
}
