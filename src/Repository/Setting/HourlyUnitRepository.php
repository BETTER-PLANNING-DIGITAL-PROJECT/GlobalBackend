<?php

namespace App\Repository\Setting;

use App\Entity\Setting\HourlyUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HourlyUnit>
 *
 * @method HourlyUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method HourlyUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method HourlyUnit[]    findAll()
 * @method HourlyUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HourlyUnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HourlyUnit::class);
    }

    public function save(HourlyUnit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HourlyUnit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HourlyUnit[] Returns an array of HourlyUnit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HourlyUnit
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
