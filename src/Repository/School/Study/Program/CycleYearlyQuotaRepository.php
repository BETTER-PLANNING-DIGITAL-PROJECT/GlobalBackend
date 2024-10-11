<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\CycleYearlyQuota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CycleYearlyQuota>
 *
 * @method CycleYearlyQuota|null find($id, $lockMode = null, $lockVersion = null)
 * @method CycleYearlyQuota|null findOneBy(array $criteria, array $orderBy = null)
 * @method CycleYearlyQuota[]    findAll()
 * @method CycleYearlyQuota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CycleYearlyQuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CycleYearlyQuota::class);
    }

    public function save(CycleYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CycleYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CycleYearlyQuota[] Returns an array of CycleYearlyQuota objects
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

//    public function findOneBySomeField($value): ?CycleYearlyQuota
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
