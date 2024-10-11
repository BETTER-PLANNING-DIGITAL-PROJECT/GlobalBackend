<?php

namespace App\Repository\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAveragePeriodCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualGeneralAveragePeriodCalculationRelation>
 *
 * @method MarkAnnualGeneralAveragePeriodCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualGeneralAveragePeriodCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualGeneralAveragePeriodCalculationRelation[]    findAll()
 * @method MarkAnnualGeneralAveragePeriodCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualGeneralAveragePeriodCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualGeneralAveragePeriodCalculationRelation::class);
    }

    public function save(MarkAnnualGeneralAveragePeriodCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualGeneralAveragePeriodCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualGeneralAveragePeriodCalculationRelation[] Returns an array of MarkAnnualGeneralAveragePeriodCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualGeneralAveragePeriodCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
