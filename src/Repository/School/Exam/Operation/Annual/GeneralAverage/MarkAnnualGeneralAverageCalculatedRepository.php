<?php

namespace App\Repository\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculated;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkAnnualGeneralAverageCalculated>
 *
 * @method MarkAnnualGeneralAverageCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkAnnualGeneralAverageCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkAnnualGeneralAverageCalculated[]    findAll()
 * @method MarkAnnualGeneralAverageCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkAnnualGeneralAverageCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkAnnualGeneralAverageCalculated::class);
    }

    public function save(MarkAnnualGeneralAverageCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkAnnualGeneralAverageCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkAnnualGeneralAverageCalculated[] Returns an array of MarkAnnualGeneralAverageCalculated objects
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

//    public function findOneBySomeField($value): ?MarkAnnualGeneralAverageCalculated
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
