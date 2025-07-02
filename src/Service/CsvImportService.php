<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CsvImportService
{
    private $entityManager;
    private $categorieRepository;

    public function __construct(EntityManagerInterface $entityManager, CategorieRepository $categorieRepository)
    {
        $this->entityManager = $entityManager;
        $this->categorieRepository = $categorieRepository;
    }

    public function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row = array_combine($headers, $data);
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }

    public function importCsv(array $csvData, array $mapping, UserInterface $user): array
    {
        $success = 0;
        foreach ($csvData as $row) {
            $transaction = new Transaction();
            $transaction->setUser($user); // Utilisation directe de $user
            if ($mapping['date'] && isset($row[$mapping['date']])) {
                $transaction->setDate(new \DateTime($row[$mapping['date']]));
            }
            if ($mapping['montant'] && isset($row[$mapping['montant']])) {
                $transaction->setMontant(floatval(str_replace(',', '.', $row[$mapping['montant']])));
            }
            if ($mapping['categorie'] && isset($row[$mapping['categorie']])) {
                $categorie = $this->categorieRepository->findOneBy(['nom' => $row[$mapping['categorie']], 'user' => $user]);
                if ($categorie) {
                    $transaction->setCategorie($categorie);
                }
            }
            $this->entityManager->persist($transaction);
            $success++;
        }
        $this->entityManager->flush();
        return ['success' => $success];
    }
}
