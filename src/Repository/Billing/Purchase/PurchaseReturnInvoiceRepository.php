<?php

namespace App\Repository\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseReturnInvoice>
 *
 * @method PurchaseReturnInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseReturnInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseReturnInvoice[]    findAll()
 * @method PurchaseReturnInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseReturnInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseReturnInvoice::class);
    }

    public function save(PurchaseReturnInvoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseReturnInvoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PurchaseReturnInvoice[] Returns an array of PurchaseReturnInvoice objects
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

//    public function findOneBySomeField($value): ?PurchaseReturnInvoice
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return PurchaseReturnInvoice[] Returns an array of PurchaseReturnInvoice objects
     */
    public function getPurchaseReturnInvoiceBySupplier():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.supplier IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function getPurchaseReturnInvoiceByCustomer():array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.customer IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
