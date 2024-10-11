<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\CycleWeighting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CycleWeighting>
 *
 * @method CycleWeighting|null find($id, $lockMode = null, $lockVersion = null)
 * @method CycleWeighting|null findOneBy(array $criteria, array $orderBy = null)
 * @method CycleWeighting[]    findAll()
 * @method CycleWeighting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CycleWeightingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CycleWeighting::class);
    }

    public function save(CycleWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CycleWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CycleWeighting[] Returns an array of CycleWeighting objects
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

//    public function findOneBySomeField($value): ?CycleWeighting
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
