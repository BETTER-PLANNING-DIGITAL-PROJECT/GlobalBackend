<?php

namespace App\Repository\School\Exam\Operation\Annual\Module;

use App\Entity\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualModuleCalculationRelation>
 *
 * @method MarkAnnualModuleCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualModuleCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualModuleCalculationRelation[]    findAll()
 * @method MarkAnnualModuleCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualModuleCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualModuleCalculationRelation::class);
    }

    public function save(MarkAnnualModuleCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualModuleCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualModuleCalculationRelation[] Returns an array of MarkAnnualModuleCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkAnnualModuleCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
