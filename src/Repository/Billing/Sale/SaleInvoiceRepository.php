<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleInvoice>
 *
 * @method SaleInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleInvoice[]    findAll()
 * @method SaleInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleInvoice::class);
    }

    public function save(SaleInvoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleInvoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleInvoice[] Returns an array of SaleInvoice objects
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

//    public function findOneBySomeField($value): ?SaleInvoice
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return SaleInvoice[] Returns an array of SaleInvoice objects
     */
    public function getSaleInvoice():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.customer IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
