<?php

namespace App\Repository\School\Exam\Operation\Annual\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseSequenceCalculationRelation;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualCourseSequenceCalculationRelation>
 *
 * @method MarkAnnualCourseSequenceCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualCourseSequenceCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualCourseSequenceCalculationRelation[]    findAll()
 * @method MarkAnnualCourseSequenceCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualCourseSequenceCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualCourseSequenceCalculationRelation::class);
    }

    public function save(MarkAnnualCourseSequenceCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualCourseSequenceCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MarkAnnualCourseSequenceCalculationRelation[] Returns an array of MarkSequenceCourseCalculated objects
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
//     * @return MarkAnnualCourseSequenceCalculationRelation[] Returns an array of MarkAnnualCourseSequenceCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualCourseSequenceCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
