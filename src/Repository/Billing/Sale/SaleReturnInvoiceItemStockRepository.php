<?php

namespace App\Repository\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoiceItemStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleReturnInvoiceItemStock>
 *
 * @method SaleReturnInvoiceItemStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleReturnInvoiceItemStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleReturnInvoiceItemStock[]    findAll()
 * @method SaleReturnInvoiceItemStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleReturnInvoiceItemStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleReturnInvoiceItemStock::class);
    }

    public function save(SaleReturnInvoiceItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleReturnInvoiceItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SaleReturnInvoiceItemStock[] Returns an array of SaleReturnInvoiceItemStock objects
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

//    public function findOneBySomeField($value): ?SaleReturnInvoiceItemStock
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
