<?php

namespace App\Repository\School\Schooling\Registration;

use App\Entity\School\Schooling\Registration\StudentPreRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentPreRegistration>
 *
 * @method StudentPreRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentPreRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentPreRegistration[]    findAll()
 * @method StudentPreRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentPreRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentPreRegistration::class);
    }

    public function save(StudentPreRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentPreRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StudentPreRegistration[] Returns an array of StudentPreRegistration objects
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

//    public function findOneBySomeField($value): ?StudentPreRegistration
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
