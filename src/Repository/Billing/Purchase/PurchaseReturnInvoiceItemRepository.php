<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseReturnInvoiceItem>
 *
 * @method PurchaseReturnInvoiceItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseReturnInvoiceItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseReturnInvoiceItem[]    findAll()
 * @method PurchaseReturnInvoiceItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseReturnInvoiceItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseReturnInvoiceItem::class);
    }

    public function save(PurchaseReturnInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseReturnInvoiceItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseReturnInvoiceItem[] Returns an array of PurchaseReturnInvoiceItem objects
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

//    public function findOneBySomeField($value): ?PurchaseReturnInvoiceItem
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

    public function sumAmountByInvoice($value):array
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

    public function purchaseReturnInvoiceHtAmount($value):array
    {
        return $this->createQueryBuilder('s')
            ->select('sum(s.amount)')
            ->andWhere('s.purchaseReturnInvoice = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getScalarResult()
            ;
    }

}
