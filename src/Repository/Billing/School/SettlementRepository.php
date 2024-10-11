<?php

namespace App\Repository\Billing\School;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Settlement>
 *
 * @method Settlement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Settlement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Settlement[]    findAll()
 * @method Settlement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettlementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Settlement::class);
    }

    public function save(Settlement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Settlement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return collection[] Returns an array of collection objects
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

//    public function findOneBySomeField($value): ?collection
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function sumSettlementAmountByInvoice($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amountPay)')
            ->andWhere('s.saleInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    public function sumSettlementValidatedAmountByInvoice($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amountPay)')
            ->andWhere('s.invoice = :val')
            ->andWhere('s.isValidate = :true')
            ->setParameter('val', $value)
            ->setParameter('true', true)
            ->getQuery()
            ->getScalarResult()
            ;
    }
}
