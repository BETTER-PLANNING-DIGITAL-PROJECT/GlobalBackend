<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Schooling\Attendance\StudentAttendance;
use App\Entity\School\Schooling\Attendance\StudentAttendanceDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentAttendance>
 *
 * @method StudentAttendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentAttendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentAttendance[]    findAll()
 * @method StudentAttendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentAttendance::class);
    }

    public function save(StudentAttendance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentAttendance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StudentAttendance[] Returns an array of StudentAttendance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StudentAttendance
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
//

    public function findWhereProgramIsNull()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.classProgram IS NULL')
            ->getQuery()
            ->getResult()
            ;
    }

    public function countPresentStudent($value, $attendance)
    {
        return $this->createQueryBuilder('s')
            ->select('count(d.id)')
            ->innerJoin(StudentAttendanceDetail::class, 'd', 'WITH','s.id = d.studentAttendance')
            ->andWhere('d.isPresent = :val')
            ->andWhere('d.studentAttendance = :att')
            ->setParameter('val', $value)
            ->setParameter('att', $attendance)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    public function countAbsentStudent($value, $attendance)
    {
        return $this->createQueryBuilder('s')
            ->select('count(d.id)')
            ->innerJoin(StudentAttendanceDetail::class, 'd', 'WITH','s.id = d.studentAttendance')
            ->andWhere('d.isPresent = :val')
            ->andWhere('d.studentAttendance = :att')
            ->setParameter('val', $value)
            ->setParameter('att', $attendance)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @return StudentAttendance[] Returns an array of StudentAttendance objects
     */
    public function findByCourse($teacher): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT (c.id), c.nameuvc as course, t.name as teacher')
            ->innerJoin('s.classProgram', 'c', 'WITH', 'c.id = s.classProgram')
            ->innerJoin('s.teacher', 't', 'WITH', 't.id = s.teacher')
            ->andWhere('s.teacher = :val')
            ->setParameter('val', $teacher)
            ->getQuery()
            ->getResult()
            ;
    }
}
