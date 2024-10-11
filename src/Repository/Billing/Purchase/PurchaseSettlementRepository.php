<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseSettlement>
 *
 * @method PurchaseSettlement|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseSettlement|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseSettlement[]    findAll()
 * @method PurchaseSettlement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseSettlementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseSettlement::class);
    }

    public function save(PurchaseSettlement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseSettlement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseSettlement[] Returns an array of PurchaseSettlement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PurchaseSettlement
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
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

    public function sumSettlementValidatedAmountByPurchaseInvoice($value):array
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

    public function sumSettlementValidatedAmountByPurchaseReturnInvoice($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amountPay)')
            ->andWhere('s.purchaseReturnInvoice = :val')
            ->andWhere('s.isValidate = :true')
            ->setParameter('val', $value)
            ->setParameter('true', true)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    /**
     * @return PurchaseSettlement[] Returns an array of PurchaseSettlement objects
     */
    public function getSettlementBySupplier():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.supplier IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function getSettlementByCustomer():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.customer IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }


}
