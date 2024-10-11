<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleSettlement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleSettlement>
 *
 * @method SaleSettlement|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleSettlement|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleSettlement[]    findAll()
 * @method SaleSettlement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleSettlementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleSettlement::class);
    }

    public function save(SaleSettlement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleSettlement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
	
	   public function countSettlements(): int
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return SaleSettlement[] Returns an array of SaleSettlement objects
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

//    public function findOneBySomeField($value): ?SaleSettlement
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function sumReturnSettlementValidatedAmountByInvoice($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amountPay)')
            ->andWhere('s.saleReturnInvoice = :val')
            ->andWhere('s.isValidate = :true')
            ->setParameter('val', $value)
            ->setParameter('true', true)
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

    /**
     * @return SaleSettlement[] Returns an array of SaleSettlement objects
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

    /**
     * @return SaleSettlement[] Returns an array of SaleSettlement objects
     */
    public function getSettlementByStudentRegistration():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.studentRegistration IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }


}
