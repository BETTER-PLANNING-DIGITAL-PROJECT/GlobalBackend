<?php

namespace App\Repository\School\Study\Teacher;

use App\Entity\School\Study\Teacher\CoursePermutation;
use App\Entity\School\Study\TimeTable\TimeTableModelDayCell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoursePermutation>
 *
 * @method CoursePermutation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursePermutation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursePermutation[]    findAll()
 * @method CoursePermutation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursePermutationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursePermutation::class);
    }

    public function save(CoursePermutation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoursePermutation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * @return CoursePermutation[] Returns an array of TimeTableModelDayCell objects
     */
    public function findByTeacher($teacher): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.course', 'c', 'WITH', 'p.course = c.id')
            ->andWhere('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return CoursePostponement[] Returns an array of CoursePostponement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CoursePostponement
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function countCourseSwap(): int
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
