<?php

namespace App\Repository\School\Study\TimeTable;

use App\Entity\School\Study\TimeTable\TimeTableModelDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeTableModelDay>
 *
 * @method TimeTableModelDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTableModelDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTableModelDay[]    findAll()
 * @method TimeTableModelDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeTableModelDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeTableModelDay::class);
    }

    public function save(TimeTableModelDay $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TimeTableModelDay $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TimeTableModelDay[] Returns an array of TimeTableModelDay objects
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

//    public function findOneBySomeField($value): ?TimeTableModelDay
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
