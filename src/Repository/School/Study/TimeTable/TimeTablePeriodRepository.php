<?php

namespace App\Repository\School\Study\TimeTable;

use App\Entity\School\Study\TimeTable\TimeTablePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeTablePeriod>
 *
 * @method TimeTablePeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTablePeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTablePeriod[]    findAll()
 * @method TimeTablePeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeTablePeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeTablePeriod::class);
    }

    public function save(TimeTablePeriod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TimeTablePeriod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TimeTablePeriod[] Returns an array of TimeTablePeriod objects
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

//    public function findOneBySomeField($value): ?TimeTablePeriod
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
