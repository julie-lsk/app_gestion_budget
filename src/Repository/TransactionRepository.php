<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function getRevenusParMois($user)
    {
        // 1. Vérifier l'objet user
        file_put_contents(__DIR__.'/debug-user.txt', "ID: " . $user->getId() . "\nEmail: " . $user->getEmail());

        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT
            DATE_FORMAT(t.date, '%Y-%m') AS mois,
            SUM(t.montant) AS total
        FROM transaction t
        WHERE t.user_id = :user
          AND t.type = 'Revenu'
        GROUP BY mois
        ORDER BY mois ASC
    ";

        // 2. Vérifier l'ID utilisateur transmis
        file_put_contents(__DIR__.'/debug-userid.txt', print_r($user->getId(), true));

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'user' => $user->getId(),
        ]);
        // 3. Vérifier le résultat SQL
        $debug = $result->fetchAllAssociative();
        file_put_contents(__DIR__.'/debug-sql-result.txt', print_r($debug, true));

        return $debug;

        //debug
        //file_put_contents(__DIR__.'/debug-getRevenusParMois.txt', print_r($result->fetchAllAssociative(), true));

        //$data = $result->fetchAllAssociative();
        //file_put_contents(__DIR__.'/debug-getRevenusParMois.txt', print_r($data, true));
        //return $data;

        //return $result->fetchAllAssociative();
    }

}
