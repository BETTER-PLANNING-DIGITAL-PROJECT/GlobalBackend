<?php

namespace App\Repository\School\Exam\Operation\Sequence\Module;

use App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkSequenceModuleCalculationRelation>
 *
 * @method MarkSequenceModuleCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkSequenceModuleCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkSequenceModuleCalculationRelation[]    findAll()
 * @method MarkSequenceModuleCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkSequenceModuleCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkSequenceModuleCalculationRelation::class);
    }

    public function save(MarkSequenceModuleCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkSequenceModuleCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkSequenceModuleCalculationRelation[] Returns an array of MarkSequenceModuleCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkSequenceModuleCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
