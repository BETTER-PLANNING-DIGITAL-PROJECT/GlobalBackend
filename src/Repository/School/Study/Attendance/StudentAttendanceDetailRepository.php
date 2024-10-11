<?php

namespace App\Repository\School\Study\Attendance;

use App\Entity\School\Study\Attendance\StudentAttendanceDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentAttendanceDetail>
 *
 * @method StudentAttendanceDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentAttendanceDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentAttendanceDetail[]    findAll()
 * @method StudentAttendanceDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentAttendanceDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentAttendanceDetail::class);
    }

    public function save(StudentAttendanceDetail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentAttendanceDetail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StudentAttendanceDetail[] Returns an array of StudentAttendanceDetail objects
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

//    public function findOneBySomeField($value): ?StudentAttendanceDetail
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
