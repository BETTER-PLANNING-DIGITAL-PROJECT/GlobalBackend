<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\ClassWeighting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassWeighting>
 *
 * @method ClassWeighting|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassWeighting|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassWeighting[]    findAll()
 * @method ClassWeighting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassWeightingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassWeighting::class);
    }

    public function save(ClassWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClassWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ClassWeighting[] Returns an array of ClassWeighting objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ClassWeighting
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
