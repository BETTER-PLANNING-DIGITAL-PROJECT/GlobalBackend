<?php

namespace App\Repository\Setting\Hr;

use App\Entity\Setting\Hr\EmploymentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmploymentStatus>
 *
 * @method EmploymentStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmploymentStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmploymentStatus[]    findAll()
 * @method EmploymentStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmploymentStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmploymentStatus::class);
    }

    public function save(EmploymentStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmploymentStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return EmploymentStatus[] Returns an array of EmploymentStatus objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmploymentStatus
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
