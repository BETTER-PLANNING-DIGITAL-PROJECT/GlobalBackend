<?php

namespace App\Repository\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCourseTernaryCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualGeneralAverageCourseTernaryCalculationRelation>
 *
 * @method MarkAnnualGeneralAverageCourseTernaryCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualGeneralAverageCourseTernaryCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualGeneralAverageCourseTernaryCalculationRelation[]    findAll()
 * @method MarkAnnualGeneralAverageCourseTernaryCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualGeneralAverageCourseTernaryCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualGeneralAverageCourseTernaryCalculationRelation::class);
    }

    public function save(MarkAnnualGeneralAverageCourseTernaryCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualGeneralAverageCourseTernaryCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualGeneralAverageCourseTernaryCalculationRelation[] Returns an array of MarkAnnualGeneralAverageCourseTernaryCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualGeneralAverageCourseTernaryCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
