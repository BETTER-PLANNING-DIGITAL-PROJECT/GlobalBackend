<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleReturnInvoice>
 *
 * @method SaleReturnInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleReturnInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleReturnInvoice[]    findAll()
 * @method SaleReturnInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleReturnInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleReturnInvoice::class);
    }

    public function save(SaleReturnInvoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleReturnInvoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleReturnInvoice[] Returns an array of SaleReturnInvoice objects
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

//    public function findOneBySomeField($value): ?SaleReturnInvoice
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return SaleReturnInvoice[] Returns an array of SaleReturnInvoice objects
     */
    public function getSaleReturnInvoiceBySupplier():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.supplier IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function getSaleReturnInvoiceByCustomer():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.customer IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
