<?php

namespace App\Repository\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Fee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fee>
 *
 * @method Fee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fee[]    findAll()
 * @method Fee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fee::class);
    }

    public function save(Fee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Fee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countFees(): int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Fee[] Returns an array of Fee objects
     */
    public function findFeeByClass($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.class = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Fee[] Returns an array of Fee objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Fee
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
