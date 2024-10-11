<?php

namespace App\Repository\School\Schooling\Registration;

use App\Entity\School\Schooling\Registration\SchoolOrigin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SchoolOrigin>
 *
 * @method SchoolOrigin|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolOrigin|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolOrigin[]    findAll()
 * @method SchoolOrigin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolOriginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchoolOrigin::class);
    }

    public function save(SchoolOrigin $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SchoolOrigin $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SchoolOrigin[] Returns an array of SchoolOrigin objects
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

//    public function findOneBySomeField($value): ?SchoolOrigin
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
