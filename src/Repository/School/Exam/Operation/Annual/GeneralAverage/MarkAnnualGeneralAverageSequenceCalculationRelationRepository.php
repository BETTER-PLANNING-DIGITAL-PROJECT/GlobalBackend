<?php

namespace App\Repository\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageSequenceCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualGeneralAverageSequenceCalculationRelation>
 *
 * @method MarkAnnualGeneralAverageSequenceCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualGeneralAverageSequenceCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualGeneralAverageSequenceCalculationRelation[]    findAll()
 * @method MarkAnnualGeneralAverageSequenceCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualGeneralAverageSequenceCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualGeneralAverageSequenceCalculationRelation::class);
    }

    public function save(MarkAnnualGeneralAverageSequenceCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualGeneralAverageSequenceCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualGeneralAverageSequenceCalculationRelation[] Returns an array of MarkAnnualGeneralAverageSequenceCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualGeneralAverageSequenceCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
