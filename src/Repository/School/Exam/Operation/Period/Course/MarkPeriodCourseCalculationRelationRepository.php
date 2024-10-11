<?php

namespace App\Repository\School\Exam\Operation\Period\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculationRelation;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodCourseCalculationRelation>
 *
 * @method MarkPeriodCourseCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkPeriodCourseCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodCourseCalculationRelation[]    findAll()
 * @method MarkPeriodCourseCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodCourseCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkPeriodCourseCalculationRelation::class);
    }

    public function save(MarkPeriodCourseCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodCourseCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MarkPeriodCourseCalculationRelation[] Returns an array of MarkCourseCalculated objects
     */
    public function getIds(SchoolClass $class,EvaluationPeriod $evaluationPeriod): array
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.sequence) as seqId')
            ->innerJoin('m.class','class')
            ->innerJoin('m.evaluationPeriod','evaluationPeriod')
            ->distinct()
            ->andWhere('class = :class')
            ->andWhere('evaluationPeriod = :evaluationPeriod')
            ->setParameter('class', $class)
            ->setParameter('evaluationPeriod', $evaluationPeriod)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return MarkPeriodCourseCalculationRelation[] Returns an array of MarkPeriodCourseCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkPeriodCourseCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
