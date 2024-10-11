<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoiceItem;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseInvoiceItem>
 *
 * @method PurchaseInvoiceItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseInvoiceItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseInvoiceItem[]    findAll()
 * @method PurchaseInvoiceItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseInvoiceItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseInvoiceItem::class);
    }

    public function save(PurchaseInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseInvoiceItem[] Returns an array of PurchaseInvoiceItem objects
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

//    public function findOneBySomeField($value): ?PurchaseInvoiceItem
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
            ->andWhere('s.saleInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    public function purchaseInvoiceTotalAmount($value):array
    {
       return $this->createQueryBuilder('s')
            ->select('sum(s.amount)')
            ->andWhere('s.purchaseInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
        ;
    }

    public function purchaseInvoiceHtAmount($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amount)')
            ->andWhere('s.purchaseInvoice = :val')
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
     * @return PurchaseInvoiceItem[] Returns an array of SaleInvoiceItem objects
     */
    public function findPurchaseInvoiceItemByPositionASC($value)
    {
        return $this->createQueryBuilder('s')
            //->select('sum(s.amount)')
            ->innerJoin('s.item', 'i', 'WITH' , 'i.id = s.item')
            ->andWhere('s.purchaseInvoice = :val')
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
