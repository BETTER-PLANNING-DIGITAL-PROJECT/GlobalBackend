<?php

namespace App\Repository\Product;

use App\Entity\Product\BillingPolicy;
use App\Entity\Product\UnitCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BillingPolicy>
 *
 * @method BillingPolicy|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingPolicy|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingPolicy[]    findAll()
 * @method BillingPolicy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingPolicyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BillingPolicy::class);
    }

    public function save(BillingPolicy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BillingPolicy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return BillingPolicy[] Returns an array of BillingPolicy objects
     */
    public function findByInstitution($institution): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.institution = :institution')
            ->setParameter('institution', $institution)
            ->orderBy('b.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return BillingPolicy[] Returns an array of BillingPolicy objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BillingPolicy
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
