<?php

namespace App\Repository\School\Exam\Operation\Sequence\GeneralAverage;

use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkSequenceGeneralAverageCalculationRelation>
 *
 * @method MarkSequenceGeneralAverageCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkSequenceGeneralAverageCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkSequenceGeneralAverageCalculationRelation[]    findAll()
 * @method MarkSequenceGeneralAverageCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkSequenceGeneralAverageCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkSequenceGeneralAverageCalculationRelation::class);
    }

    public function save(MarkSequenceGeneralAverageCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkSequenceGeneralAverageCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkSequenceGeneralAverageCalculationRelation[] Returns an array of MarkSequenceGeneralAverageCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkSequenceGeneralAverageCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
