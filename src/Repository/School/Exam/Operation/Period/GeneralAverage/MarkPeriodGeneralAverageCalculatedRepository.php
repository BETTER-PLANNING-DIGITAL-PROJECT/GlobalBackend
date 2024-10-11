<?php

namespace App\Repository\School\Exam\Operation\Period\GeneralAverage;

use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodGeneralAverageCalculated>
 *
 * @method MarkPeriodGeneralAverageCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkPeriodGeneralAverageCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodGeneralAverageCalculated[]    findAll()
 * @method MarkPeriodGeneralAverageCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodGeneralAverageCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkPeriodGeneralAverageCalculated::class);
    }

    public function save(MarkPeriodGeneralAverageCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodGeneralAverageCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkPeriodGeneralAverageCalculated[] Returns an array of MarkPeriodGeneralAverageCalculated objects
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

//    public function findOneBySomeField($value): ?MarkPeriodGeneralAverageCalculated
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
