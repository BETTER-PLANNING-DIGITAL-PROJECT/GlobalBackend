<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\SpecialityYearlyQuota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpecialityYearlyQuota>
 *
 * @method SpecialityYearlyQuota|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecialityYearlyQuota|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecialityYearlyQuota[]    findAll()
 * @method SpecialityYearlyQuota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialityYearlyQuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecialityYearlyQuota::class);
    }

    public function save(SpecialityYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SpecialityYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SpecialityYearlyQuota[] Returns an array of SpecialityYearlyQuota objects
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

//    public function findOneBySomeField($value): ?SpecialityYearlyQuota
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
