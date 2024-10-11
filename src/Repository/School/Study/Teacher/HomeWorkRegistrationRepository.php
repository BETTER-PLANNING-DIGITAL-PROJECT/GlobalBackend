<?php

namespace App\Repository\School\Study\Teacher;

use App\Entity\School\Study\Teacher\HomeWorkRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeWorkRegistration>
 *
 * @method HomeWorkRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeWorkRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeWorkRegistration[]    findAll()
 * @method HomeWorkRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeWorkRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeWorkRegistration::class);
    }

    public function save(HomeWorkRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HomeWorkRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return HomeWorkRegistration[] Returns an array of HomeWorkStudentReply objects
     */
    public function findByStudent($student): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.homeWork', 'h', 'WITH', 'h.id = r.homeWork')
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return HomeWorkRegistration[] Returns an array of HomeWorkRegistration objects
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

//    public function findOneBySomeField($value): ?HomeWorkRegistration
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
