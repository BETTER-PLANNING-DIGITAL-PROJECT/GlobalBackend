<?php

namespace App\Repository\Product;

use App\Entity\Product\SubtractStrategy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubtractStrategy>
 *
 * @method SubtractStrategy|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubtractStrategy|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubtractStrategy[]    findAll()
 * @method SubtractStrategy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubtractStrategyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubtractStrategy::class);
    }

    public function save(SubtractStrategy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SubtractStrategy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SubtractStrategy[] Returns an array of SubtractStrategy objects
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

//    public function findOneBySomeField($value): ?SubtractStrategy
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
