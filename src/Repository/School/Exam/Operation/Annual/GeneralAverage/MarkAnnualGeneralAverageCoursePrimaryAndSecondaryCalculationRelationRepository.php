<?php

namespace App\Repository\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation>
 *
 * @method MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation[]    findAll()
 * @method MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation::class);
    }

    public function save(MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation[] Returns an array of MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
