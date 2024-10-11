<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleInvoiceItemTax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleInvoiceItemTax>
 *
 * @method SaleInvoiceItemTax|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleInvoiceItemTax|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleInvoiceItemTax[]    findAll()
 * @method SaleInvoiceItemTax[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleInvoiceItemTaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleInvoiceItemTax::class);
    }

    public function save(SaleInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleInvoiceItemTax[] Returns an array of SaleInvoiceItemTax objects
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

//    public function findOneBySomeField($value): ?SaleInvoiceItemTax
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
