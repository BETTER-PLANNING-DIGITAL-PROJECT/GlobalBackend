<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleInvoiceItemDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleInvoiceItemDiscount>
 *
 * @method SaleInvoiceItemDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleInvoiceItemDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleInvoiceItemDiscount[]    findAll()
 * @method SaleInvoiceItemDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleInvoiceItemDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleInvoiceItemDiscount::class);
    }

    public function save(SaleInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleInvoiceItemDiscount[] Returns an array of SaleInvoiceItemDiscount objects
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

//    public function findOneBySomeField($value): ?SaleInvoiceItemDiscount
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
