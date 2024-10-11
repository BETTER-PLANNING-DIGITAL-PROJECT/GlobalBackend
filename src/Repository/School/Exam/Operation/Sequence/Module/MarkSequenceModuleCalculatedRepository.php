<?php

namespace App\Repository\School\Exam\Operation\Sequence\Module;

use App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkSequenceModuleCalculated>
 *
 * @method MarkSequenceModuleCalculated|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkSequenceModuleCalculated[]    findAll()
 * @method \App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkSequenceModuleCalculatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated::class);
    }

    public function save(\App\Entity\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkSequenceModuleCalculated $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkSequenceModuleCalculated[] Returns an array of MarkSequenceModuleCalculated objects
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

//    public function findOneBySomeField($value): ?MarkSequenceModuleCalculated
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
