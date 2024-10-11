<?php

namespace App\Repository\Partner;

use App\Entity\Partner\StudentRegistrationHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentRegistrationHistory>
 *
 * @method StudentRegistrationHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentRegistrationHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentRegistrationHistory[]    findAll()
 * @method StudentRegistrationHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRegistrationHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentRegistrationHistory::class);
    }

    public function save(StudentRegistrationHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentRegistrationHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StudentRegistrationHistory[] Returns an array of StudentRegistrationHistory objects
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

//    public function findOneBySomeField($value): ?StudentRegistrationHistory
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
