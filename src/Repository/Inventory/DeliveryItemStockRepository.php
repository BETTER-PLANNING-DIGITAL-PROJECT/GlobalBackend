<?php

namespace App\Repository\Inventory;

use App\Entity\Inventory\DeliveryItemStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliveryItemStock>
 *
 * @method DeliveryItemStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryItemStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryItemStock[]    findAll()
 * @method DeliveryItemStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryItemStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryItemStock::class);
    }

    public function save(DeliveryItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DeliveryItemStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DeliveryItem[] Returns an array of DeliveryItem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DeliveryItem
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
