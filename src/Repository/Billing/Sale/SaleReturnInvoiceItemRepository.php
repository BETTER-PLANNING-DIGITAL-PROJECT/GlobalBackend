<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleReturnInvoiceItem>
 *
 * @method SaleReturnInvoiceItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleReturnInvoiceItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleReturnInvoiceItem[]    findAll()
 * @method SaleReturnInvoiceItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleReturnInvoiceItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleReturnInvoiceItem::class);
    }

    public function save(SaleReturnInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleReturnInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleReturnInvoiceItem[] Returns an array of SaleReturnInvoiceItem objects
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

//    public function findOneBySomeField($value): ?SaleReturnInvoiceItem
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
     * @return SaleReturnInvoiceItem[] Returns an array of SaleInvoiceItem objects
     */
    public function findSaleReturnInvoiceItemByPositionASC($value)
    {
        return $this->createQueryBuilder('s')
            //->select('sum(s.amount)')
            ->innerJoin('s.item', 'i', 'WITH' , 'i.id = s.item')
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
