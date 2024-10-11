<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\StudentCoursePlanRegCall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentCoursePlanRegCall>
 *
 * @method StudentCoursePlanRegCall|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentCoursePlanRegCall|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentCoursePlanRegCall[]    findAll()
 * @method StudentCoursePlanRegCall[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentCoursePlanRegCallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentCoursePlanRegCall::class);
    }

    public function save(StudentCoursePlanRegCall $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentCoursePlanRegCall $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StudentCoursePlanRegCall[] Returns an array of StudentCoursePlanRegCall objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StudentCoursePlanRegCall
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
