<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoiceItemStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseInvoiceItemStock>
 *
 * @method PurchaseInvoiceItemStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseInvoiceItemStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseInvoiceItemStock[]    findAll()
 * @method PurchaseInvoiceItemStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseInvoiceItemStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseInvoiceItemStock::class);
    }

    public function save(PurchaseInvoiceItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseInvoiceItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseInvoiceItemStock[] Returns an array of PurchaseInvoiceItemStock objects
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

//    public function findOneBySomeField($value): ?PurchaseInvoiceItemStock
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
