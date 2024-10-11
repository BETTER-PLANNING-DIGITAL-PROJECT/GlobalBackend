<?php

namespace App\Repository\Inventory;

use App\Entity\Inventory\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function save(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Stock[] Returns an array of Stock objects
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

//    public function findOneBySomeField($value): ?Stock
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    /**
     * @return Stock[] Returns an array of Stock objects
     */
    public function findItemWhereStock($item,$branch): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.item = :item')
            ->andWhere('s.branch = :branch')
            ->andWhere('s.availableQte > :qty')
            ->setParameter('item', $item)
            ->setParameter('branch', $branch)
            ->setParameter('qty', 0)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneStock($value)
    {
        return $this->createQueryBuilder('s')
            ->select('sum (s.availableQte)')
            ->andWhere('s.item = :val')
			// ->andWhere('s.availableQte > :qty')
            ->setParameter('val', $value)
			// ->setParameter('qty', 0)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Stock[] Returns an array of Stock objects
     */
    public function findByItemFIFO($item): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.item = :val')
            ->andWhere('s.availableQte > :qty')
            ->setParameter('val', $item)
            ->setParameter('qty', 0)
            ->orderBy('s.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Stock[] Returns an array of Stock objects
     */
    public function findByItemLIFO($item): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.item = :val')
            ->andWhere('s.availableQte > :qty')
            ->setParameter('val', $item)
            ->setParameter('qty', 0)
            ->orderBy('s.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.item = :item')
            ->andWhere('s.branch = :branch')
            //->andWhere('s.store = :store')
            ->andWhere('s.availableQte > 0')
            ->setParameter('item', $item)
            ->setParameter('branch', $branch)
            //->setParameter('store', $store)
            ->orderBy('s.stockAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOneAvailableStockGreaterThanZeroByItemStoreDesc($item,$branch)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.item = :item')
            ->andWhere('s.branch = :branch')
            //->andWhere('s.store = :store')
            ->andWhere('s.availableQte > 0')
            ->setParameter('item', $item)
            ->setParameter('branch', $branch)
            //->setParameter('store', $store)
            ->orderBy('s.stockAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

}
