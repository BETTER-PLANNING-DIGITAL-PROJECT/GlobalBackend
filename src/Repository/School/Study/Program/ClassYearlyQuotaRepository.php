<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\ClassYearlyQuota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassYearlyQuota>
 *
 * @method ClassYearlyQuota|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassYearlyQuota|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassYearlyQuota[]    findAll()
 * @method ClassYearlyQuota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassYearlyQuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassYearlyQuota::class);
    }

    public function save(ClassYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClassYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ClassYearlyQuota[] Returns an array of ClassYearlyQuota objects
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

//    public function findOneBySomeField($value): ?ClassYearlyQuota
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
