<?php

namespace App\Repository\School\Exam\Operation\Annual\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualCourseCalculated>
 *
 * @method MarkAnnualCourseCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualCourseCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualCourseCalculated[]    findAll()
 * @method MarkAnnualCourseCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualCourseCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualCourseCalculated::class);
    }

    public function save(MarkAnnualCourseCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualCourseCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MarkAnnualCourseCalculated[] Returns an array of MarkAnnualCourseCalculated objects
     */
    public function getClassPrograms(SchoolClass $class): array
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.classProgram) as classProgramId')
            ->distinct()
            ->andWhere('m.class = :class')
            ->setParameter('class', $class)
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
