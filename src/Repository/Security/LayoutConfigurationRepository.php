<?php

namespace App\Repository\Security;

use App\Entity\Security\LayoutConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LayoutConfiguration>
 *
 * @method LayoutConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method LayoutConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method LayoutConfiguration[]    findAll()
 * @method LayoutConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LayoutConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LayoutConfiguration::class);
    }

    public function save(LayoutConfiguration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LayoutConfiguration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LayoutConfiguration[] Returns an array of LayoutConfiguration objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LayoutConfiguration
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
