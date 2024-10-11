<?php

namespace App\Repository\School\Study\Teacher;

use App\Entity\School\Study\Teacher\CoursePostponement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoursePostponement>
 *
 * @method CoursePostponement|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursePostponement|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursePostponement[]    findAll()
 * @method CoursePostponement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursePostponementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursePostponement::class);
    }

    public function save(CoursePostponement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoursePostponement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return CoursePostponement[] Returns an array of TimeTableModelDayCell objects
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

    public function countCoursePostponement(): int
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
