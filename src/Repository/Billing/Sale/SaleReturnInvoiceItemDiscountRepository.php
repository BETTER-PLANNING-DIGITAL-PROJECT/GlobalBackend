<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoiceItemDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleReturnInvoiceItemDiscount>
 *
 * @method SaleReturnInvoiceItemDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleReturnInvoiceItemDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleReturnInvoiceItemDiscount[]    findAll()
 * @method SaleReturnInvoiceItemDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleReturnInvoiceItemDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleReturnInvoiceItemDiscount::class);
    }

    public function save(SaleReturnInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleReturnInvoiceItemDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleReturnInvoiceItemDiscount[] Returns an array of SaleReturnInvoiceItemDiscount objects
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

//    public function findOneBySomeField($value): ?SaleReturnInvoiceItemDiscount
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
