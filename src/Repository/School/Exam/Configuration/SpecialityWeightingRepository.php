<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\SpecialityWeighting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpecialityWeighting>
 *
 * @method SpecialityWeighting|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecialityWeighting|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecialityWeighting[]    findAll()
 * @method SpecialityWeighting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialityWeightingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecialityWeighting::class);
    }

    public function save(SpecialityWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SpecialityWeighting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SpecialityWeighting[] Returns an array of SpecialityWeighting objects
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

//    public function findOneBySomeField($value): ?SpecialityWeighting
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
