<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\PromotionCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PromotionCondition>
 *
 * @method PromotionCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionCondition[]    findAll()
 * @method PromotionCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionCondition::class);
    }

//    /**
//     * @return PromotionConditions[] Returns an array of PromotionConditions objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PromotionConditions
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
