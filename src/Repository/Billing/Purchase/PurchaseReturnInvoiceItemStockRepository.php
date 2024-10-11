<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseReturnInvoiceItemStock>
 *
 * @method PurchaseReturnInvoiceItemStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseReturnInvoiceItemStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseReturnInvoiceItemStock[]    findAll()
 * @method PurchaseReturnInvoiceItemStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseReturnInvoiceItemStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseReturnInvoiceItemStock::class);
    }

    public function save(PurchaseReturnInvoiceItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseReturnInvoiceItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseReturnInvoiceItemStock[] Returns an array of PurchaseReturnInvoiceItemStock objects
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

//    public function findOneBySomeField($value): ?PurchaseReturnInvoiceItemStock
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
