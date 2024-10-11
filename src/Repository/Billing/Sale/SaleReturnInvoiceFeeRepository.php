<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoiceFee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleReturnInvoiceFee>
 *
 * @method SaleReturnInvoiceFee|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleReturnInvoiceFee|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleReturnInvoiceFee[]    findAll()
 * @method SaleReturnInvoiceFee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleReturnInvoiceFeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleReturnInvoiceFee::class);
    }

    public function save(SaleReturnInvoiceFee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleReturnInvoiceFee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleReturnInvoiceFee[] Returns an array of SaleReturnInvoiceFee objects
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

//    public function findOneBySomeField($value): ?SaleReturnInvoiceFee
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function sumPuByInvoice($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.pu)')
            ->andWhere('s.saleReturnInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    public function saleReturnInvoiceHtAmount($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amount)')
            ->andWhere('s.saleReturnInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    public function sumAmountWithTaxesByInvoice($value)
    {
        return $this->createQueryBuilder('s')
            //->select('sum(s.amount)')
            ->innerJoin('s.taxes', 't', 'WITH' , 't.id IS NOT NULL')
            ->andWhere('s.saleReturnInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            //->getOneOrNullResult()
            ;
    }

    /**
     * @return SaleReturnInvoiceFee[] Returns an array of SaleInvoiceItem objects
     */
    public function findSaleReturnInvoiceFeeByPositionASC($value)
    {
        return $this->createQueryBuilder('s')
            //->select('sum(s.amount)')
            ->innerJoin('s.fee', 'i', 'WITH' , 'i.id = s.fee')
            ->andWhere('s.saleReturnInvoice = :val')
            ->andWhere('s.isTreat = :boolVal')
            ->setParameter('val', $value)
            ->setParameter('boolVal', false)
            ->orderBy('i.position', 'ASC')
            ->getQuery()
            ->getResult()
            //->getOneOrNullResult()
            ;
    }

}
