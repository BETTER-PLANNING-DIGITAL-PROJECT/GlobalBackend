<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoiceItemTax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleReturnInvoiceItemTax>
 *
 * @method SaleReturnInvoiceItemTax|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleReturnInvoiceItemTax|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleReturnInvoiceItemTax[]    findAll()
 * @method SaleReturnInvoiceItemTax[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleReturnInvoiceItemTaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleReturnInvoiceItemTax::class);
    }

    public function save(SaleReturnInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleReturnInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleReturnInvoiceItemTax[] Returns an array of SaleReturnInvoiceItemTax objects
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

//    public function findOneBySomeField($value): ?SaleReturnInvoiceItemTax
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
