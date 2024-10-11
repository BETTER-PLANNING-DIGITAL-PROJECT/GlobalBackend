<?php

namespace App\Repository\School\Exam\Operation\Period\GeneralAverage;

use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageSequenceCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodGeneralAverageSequenceCalculationRelation>
 *
 * @method MarkPeriodGeneralAverageSequenceCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkPeriodGeneralAverageSequenceCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodGeneralAverageSequenceCalculationRelation[]    findAll()
 * @method MarkPeriodGeneralAverageSequenceCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodGeneralAverageSequenceCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkPeriodGeneralAverageSequenceCalculationRelation::class);
    }

    public function save(MarkPeriodGeneralAverageSequenceCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodGeneralAverageSequenceCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkPeriodGeneralAverageSequenceCalculationRelation[] Returns an array of MarkPeriodGeneralAverageSequenceCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkPeriodGeneralAverageSequenceCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
