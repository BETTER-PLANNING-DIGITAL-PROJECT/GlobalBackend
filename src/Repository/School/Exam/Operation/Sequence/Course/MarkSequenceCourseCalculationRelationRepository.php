<?php

namespace App\Repository\School\Exam\Operation\Sequence\Course;

use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkSequenceCourseCalculationRelation>
 *
 * @method MarkSequenceCourseCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkSequenceCourseCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkSequenceCourseCalculationRelation[]    findAll()
 * @method MarkSequenceCourseCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkSequenceCourseCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkSequenceCourseCalculationRelation::class);
    }

    public function save(MarkSequenceCourseCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkSequenceCourseCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkSequenceCourseCalculationRelation[] Returns an array of MarkSequenceCourseCalculationRelation objects
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

//    public function findOneBySomeField($value): ?MarkSequenceCourseCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
