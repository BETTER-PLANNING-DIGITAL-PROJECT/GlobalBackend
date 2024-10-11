<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\SchoolWeighting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SchoolWeighting>
 *
 * @method SchoolWeighting|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolWeighting|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolWeighting[]    findAll()
 * @method SchoolWeighting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolWeightingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchoolWeighting::class);
    }

    public function save(SchoolWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SchoolWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SchoolWeighting[] Returns an array of SchoolWeighting objects
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

//    public function findOneBySomeField($value): ?SchoolWeighting
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
