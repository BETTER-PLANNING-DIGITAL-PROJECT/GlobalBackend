<?php

namespace App\Repository\School\Exam\Operation\Annual\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCoursePeriodCalculationRelation;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualCoursePeriodCalculationRelation>
 *
 * @method MarkAnnualCoursePeriodCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualCoursePeriodCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualCoursePeriodCalculationRelation[]    findAll()
 * @method MarkAnnualCoursePeriodCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualCoursePeriodCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualCoursePeriodCalculationRelation::class);
    }

    public function save(MarkAnnualCoursePeriodCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualCoursePeriodCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MarkAnnualCoursePeriodCalculationRelation[] Returns an array of MarkSequenceCourseCalculated objects
     */
    public function getSequenceIds(SchoolClass $class,EvaluationPeriod $evaluationPeriod): array
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
//     * @return MarkAnnualCoursePeriodCalculationRelation[] Returns an array of MarkAnnualCoursePeriodCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualCoursePeriodCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
