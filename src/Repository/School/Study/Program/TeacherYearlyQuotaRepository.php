<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\TeacherYearlyQuota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeacherYearlyQuota>
 *
 * @method TeacherYearlyQuota|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeacherYearlyQuota|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeacherYearlyQuota[]    findAll()
 * @method TeacherYearlyQuota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeacherYearlyQuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeacherYearlyQuota::class);
    }

    public function save(TeacherYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TeacherYearlyQuota $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TeacherYearlyQuota[] Returns an array of TeacherYearlyQuota objects
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

//    public function findOneBySomeField($value): ?TeacherYearlyQuota
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
