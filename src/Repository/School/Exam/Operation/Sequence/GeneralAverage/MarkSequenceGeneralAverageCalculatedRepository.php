<?php

namespace App\Repository\School\Exam\Operation\Sequence\GeneralAverage;

use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<\App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated>
 *
 * @method MarkSequenceGeneralAverageCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkSequenceGeneralAverageCalculated[]    findAll()
 * @method MarkSequenceGeneralAverageCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkSequenceGeneralAverageCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated::class);
    }

    public function save(\App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(\App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkSequenceGeneralAverageCalculated[] Returns an array of MarkSequenceGeneralAverageCalculated objects
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

//    public function findOneBySomeField($value): ?MarkSequenceGeneralAverageCalculated
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
