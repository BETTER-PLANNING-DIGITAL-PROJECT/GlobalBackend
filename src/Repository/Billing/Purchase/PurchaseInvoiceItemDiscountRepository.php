<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoiceItemDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseInvoiceItemDiscount>
 *
 * @method PurchaseInvoiceItemDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseInvoiceItemDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseInvoiceItemDiscount[]    findAll()
 * @method PurchaseInvoiceItemDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseInvoiceItemDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseInvoiceItemDiscount::class);
    }

    public function save(PurchaseInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseInvoiceItemDiscount[] Returns an array of PurchaseInvoiceItemDiscount objects
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

//    public function findOneBySomeField($value): ?PurchaseInvoiceItemDiscount
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
