<?php

namespace App\Repository\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\StudentFollowUp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentFollowUp>
 *
 * @method StudentFollowUp|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentFollowUp|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentFollowUp[]    findAll()
 * @method StudentFollowUp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentFollowUpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentFollowUp::class);
    }

    public function save(StudentFollowUp $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentFollowUp $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ModuleCategory[] Returns an array of ModuleCategory objects
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

//    public function findOneBySomeField($value): ?ModuleCategory
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
