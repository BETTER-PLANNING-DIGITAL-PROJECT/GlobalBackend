<?php

namespace App\Repository\School\Exam\Operation\Period\Module;

use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodModuleCalculated>
 *
 * @method MarkPeriodModuleCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodModuleCalculated[]    findAll()
 * @method \App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodModuleCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated::class);
    }

    public function save(\App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodModuleCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualModuleCalculated[] Returns an array of MarkAnnualModuleCalculated objects
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

//    public function findOneBySomeField($value): ?MarkAnnualModuleCalculated
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
