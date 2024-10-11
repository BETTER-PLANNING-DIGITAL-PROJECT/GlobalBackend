<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleInvoiceFee;
use App\Entity\Billing\Sale\SaleSettlement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleInvoiceFee>
 *
 * @method SaleInvoiceFee|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleInvoiceFee|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleInvoiceFee[]    findAll()
 * @method SaleInvoiceFee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleInvoiceFeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleInvoiceFee::class);
    }

    public function save(SaleInvoiceFee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleInvoiceFee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleInvoiceFee[] Returns an array of SaleInvoiceFee objects
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

//    public function findOneBySomeField($value): ?SaleInvoiceFee
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

//    public function findByStudentRegistration($studentRegistration, $invoice): array
//    {
//        return $this->createQueryBuilder('f')
//            ->innerJoin(SaleSettlement::class, 's', 'WITH' , 's.invoice = f.saleInvoice')
//            ->andWhere('f.studentRegistration = :val')
//            ->andWhere('f.saleInvoice = :test')
//            ->setParameter('val', $studentRegistration)
//            ->setParameter('test', $invoice)
//            ->getQuery()
//            ->getResult();
//    }

    public function findByStudentRegistration($studentRegistration, $invoice): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin(SaleSettlement::class, 's', 'WITH' , 's.invoice = f.saleInvoice')
            ->andWhere('f.studentRegistration = :val')
            ->andWhere('f.saleInvoice = :test')
            ->setParameter('val', $studentRegistration)
            ->setParameter('test', $invoice)
            ->getQuery()
            ->getResult();
    }
	



    public function sumPuByInvoice($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.pu)')
            ->andWhere('s.saleInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    public function saleInvoiceHtAmount($value):array
    {
       return $this->createQueryBuilder('s')
            ->select('sum(s.amount)')
            ->andWhere('s.saleInvoice = :val')
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
            ->andWhere('s.saleInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            //->getOneOrNullResult()
            ;
    }

    /**
     * @return SaleInvoiceFee[] Returns an array of SaleInvoiceFee objects
     */
    public function findSaleInvoiceFeeByPositionASC($value)
    {
        return $this->createQueryBuilder('s')
            //->select('sum(s.amount)')
            ->innerJoin('s.fee', 'i', 'WITH' , 'i.id = s.fee')
            ->andWhere('s.saleInvoice = :val')
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
