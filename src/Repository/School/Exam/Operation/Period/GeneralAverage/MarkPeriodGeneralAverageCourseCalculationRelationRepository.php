<?php

namespace App\Repository\School\Exam\Operation\Period\GeneralAverage;

use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCourseCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodGeneralAverageCourseCalculationRelation>
 *
 * @method MarkPeriodGeneralAverageCourseCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkPeriodGeneralAverageCourseCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodGeneralAverageCourseCalculationRelation[]    findAll()
 * @method MarkPeriodGeneralAverageCourseCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodGeneralAverageCourseCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkPeriodGeneralAverageCourseCalculationRelation::class);
    }

    public function save(MarkPeriodGeneralAverageCourseCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodGeneralAverageCourseCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkPeriodGeneralAverageCourseCalculationRelation[] Returns an array of MarkPeriodGeneralAverageCourseCalculationRelation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MarkPeriodGeneralAverageCourseCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
