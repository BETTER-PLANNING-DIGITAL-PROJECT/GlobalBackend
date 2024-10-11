<?php

namespace App\Repository\Treasury;

use App\Entity\Treasury\CashDeskHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CashDeskHistory>
 *
 * @method CashDeskHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CashDeskHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CashDeskHistory[]    findAll()
 * @method CashDeskHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CashDeskHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashDeskHistory::class);
    }

    public function save(CashDeskHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CashDeskHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CashDeskHistory[] Returns an array of CashDeskHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CashDeskHistory
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
