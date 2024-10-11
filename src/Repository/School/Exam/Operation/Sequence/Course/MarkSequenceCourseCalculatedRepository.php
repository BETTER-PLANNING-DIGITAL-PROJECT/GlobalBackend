<?php

namespace App\Repository\School\Exam\Operation\Sequence\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkSequenceCourseCalculated>
 *
 * @method MarkSequenceCourseCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkSequenceCourseCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkSequenceCourseCalculated[]    findAll()
 * @method MarkSequenceCourseCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkSequenceCourseCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkSequenceCourseCalculated::class);
    }

    public function save(MarkSequenceCourseCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkSequenceCourseCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MarkSequenceCourseCalculated[] Returns an array of MarkSequenceCourseCalculated objects
     */
    public function getClassPrograms(SchoolClass $class,EvaluationPeriod $evaluationPeriod,Sequence $sequence): array
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.classProgram) as classProgramId')
            ->distinct()
            ->andWhere('m.class = :class')
            ->andWhere('m.evaluationPeriod = :evaluationPeriod')
            ->andWhere('m.sequence = :sequence')
            ->setParameter('class', $class)
            ->setParameter('evaluationPeriod', $evaluationPeriod)
            ->setParameter('sequence', $sequence)
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
