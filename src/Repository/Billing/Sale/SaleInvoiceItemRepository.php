<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleInvoiceItem>
 *
 * @method SaleInvoiceItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleInvoiceItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleInvoiceItem[]    findAll()
 * @method SaleInvoiceItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleInvoiceItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleInvoiceItem::class);
    }

    public function save(SaleInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleInvoiceItem[] Returns an array of SaleInvoiceItem objects
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

//    public function findOneBySomeField($value): ?SaleInvoiceItem
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByStudentRegistration($studentRegistration): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.studentRegistration = :val')
            ->setParameter('val', $studentRegistration)
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
     * @return SaleInvoiceItem[] Returns an array of SaleInvoiceItem objects
     */
    public function findSaleInvoiceItemByPositionASC($value)
    {
        return $this->createQueryBuilder('s')
            //->select('sum(s.amount)')
            ->innerJoin('s.item', 'i', 'WITH' , 'i.id = s.item')
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
