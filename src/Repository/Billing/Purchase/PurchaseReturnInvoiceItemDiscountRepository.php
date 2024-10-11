<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseReturnInvoiceItemDiscount>
 *
 * @method PurchaseReturnInvoiceItemDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseReturnInvoiceItemDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseReturnInvoiceItemDiscount[]    findAll()
 * @method PurchaseReturnInvoiceItemDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseReturnInvoiceItemDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseReturnInvoiceItemDiscount::class);
    }

    public function save(PurchaseReturnInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseReturnInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseReturnInvoiceItemDiscount[] Returns an array of PurchaseReturnInvoiceItemDiscount objects
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

//    public function findOneBySomeField($value): ?PurchaseReturnInvoiceItemDiscount
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
