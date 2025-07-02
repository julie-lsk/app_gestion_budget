<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transaction;

final class DashboardController extends AbstractController
{
// src/Controller/DashboardController.php

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $connection = $em->getConnection();

        $sql = "
    SELECT MONTH(date) as mois, SUM(montant) as total
    FROM transaction
    WHERE user_id = :user_id
      AND LOWER(type) = :type
      AND YEAR(date) = :year
    GROUP BY mois
    ORDER BY mois ASC
";

        $stmt = $connection->prepare($sql);
        $result = $stmt->executeQuery([
            'user_id' => $user->getId(),
            'type' => 'revenu',
            'year' => date('Y'),
        ]);

        $rows = $result->fetchAllAssociative();

        $moisNoms = [1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Aoû', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
        $labels = [];
        $datas = [];
        foreach ($rows as $row) {
            $labels[] = $moisNoms[(int)$row['mois']];
            $datas[] = (float)$row['total'];
        }
        if (empty($labels)) {
            $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
            $datas = [0, 0, 0, 0, 0, 0];
        }
        return $this->render('dashboard/index.html.twig', [
            'revenusLabels' => json_encode($labels),
            'revenusData' => json_encode($datas),
            'depensesLabels' => json_encode(['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin']),
            'depensesData' => json_encode([0, 0, 0, 0, 0, 0]),
            'depensesCategorieLabels' => json_encode(['Loyer', 'Courses', 'Transport', 'Santé', 'Autres']),
            'depensesCategorieData' => json_encode([0, 0, 0, 0, 0]),
            'revenusCategorieLabels' => json_encode(['Salaire', 'Allocations', 'Ventes', 'Autres']),
            'revenusCategorieData' => json_encode([0, 0, 0, 0]),
        ]);

    }
}
