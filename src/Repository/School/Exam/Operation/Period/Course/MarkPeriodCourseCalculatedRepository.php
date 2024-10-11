<?php

namespace App\Repository\School\Exam\Operation\Period\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodCourseCalculated>
 *
 * @method MarkPeriodCourseCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkPeriodCourseCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodCourseCalculated[]    findAll()
 * @method MarkPeriodCourseCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodCourseCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkPeriodCourseCalculated::class);
    }

    public function save(MarkPeriodCourseCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodCourseCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MarkPeriodCourseCalculated[] Returns an array of MarkPeriodCourseCalculated objects
     */
    public function getClassPrograms(SchoolClass $class,EvaluationPeriod $evaluationPeriod): array
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.classProgram) as classProgramId')
            ->distinct()
            ->andWhere('m.class = :class')
            ->andWhere('m.evaluationPeriod = :evaluationPeriod')
            ->setParameter('class', $class)
            ->setParameter('evaluationPeriod', $evaluationPeriod)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return MarkSequenceCourseCalculated[] Returns an array of MarkSequenceCourseCalculated objects
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

//    public function findOneBySomeField($value): ?MarkSequenceCourseCalculated
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
