<?php

namespace App\Repository\School\Study\TimeTable;

use App\Entity\School\Study\TimeTable\TimeTableModelDayCell;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeTableModelDayCell>
 *
 * @method TimeTableModelDayCell|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTableModelDayCell|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTableModelDayCell[]    findAll()
 * @method TimeTableModelDayCell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeTableModelDayCellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeTableModelDayCell::class);
    }

    public function save(TimeTableModelDayCell $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TimeTableModelDayCell $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TimeTableModelDayCell[] Returns an array of TimeTableModelDayCell objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TimeTableModelDayCell
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findOneByDateCell($value): ?TimeTableModelDayCell
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.dateCell like :val')
            ->setParameter('val', $value .'%')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findTeacherCoursesDiffBySelectedCourseId($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id != :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }



    /**
     * @return int Returns the count of courses objects for the current day
     */
    public function countTodayCourses(): int
    {
        $today = new \DateTimeImmutable();
        $todayStr = $today->format('Y-m-d');

        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->where('t.date = :date')
            ->setParameter('date', $todayStr)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @return array Returns the upcoming courses objects
     */
    public function getUpcomingCourses(): array
    {
        $today = new \DateTimeImmutable();
        $time = new \DateTimeImmutable(null, new DateTimeZone('Africa/Douala'));
        $todayStr = $today->format('Y-m-d');
        $timeStr = $time->format('H:i:s');


        return $this->createQueryBuilder('t')
            ->where('t.date >= :date')
            ->andWhere('t.endAt >= :time')
            ->setParameter('date', $todayStr)
            ->setParameter('time', $timeStr)
            ->orderBy('t.date', 'ASC')
//            ->orderBy('t.startAt', 'ASC')
            ->orderBy('t.endAt', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }



}
