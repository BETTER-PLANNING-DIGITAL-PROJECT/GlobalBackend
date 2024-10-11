<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoiceItemTax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseInvoiceItemTax>
 *
 * @method PurchaseInvoiceItemTax|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseInvoiceItemTax|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseInvoiceItemTax[]    findAll()
 * @method PurchaseInvoiceItemTax[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseInvoiceItemTaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseInvoiceItemTax::class);
    }

    public function save(PurchaseInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseInvoiceItemTax $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseInvoiceItemTax[] Returns an array of PurchaseInvoiceItemTax objects
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

//    public function findOneBySomeField($value): ?PurchaseInvoiceItemTax
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
