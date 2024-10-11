<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemTax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseReturnInvoiceItemTax>
 *
 * @method PurchaseReturnInvoiceItemTax|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseReturnInvoiceItemTax|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseReturnInvoiceItemTax[]    findAll()
 * @method PurchaseReturnInvoiceItemTax[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseReturnInvoiceItemTaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseReturnInvoiceItemTax::class);
    }

    public function save(PurchaseReturnInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseReturnInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseReturnInvoiceItemTax[] Returns an array of PurchaseReturnInvoiceItemTax objects
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

//    public function findOneBySomeField($value): ?PurchaseReturnInvoiceItemTax
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
