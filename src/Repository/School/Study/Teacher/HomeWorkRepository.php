<?php

namespace App\Repository\School\Study\Teacher;

use App\Entity\School\Study\Teacher\HomeWork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeWork>
 *
 * @method HomeWork|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeWork|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeWork[]    findAll()
 * @method HomeWork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeWorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeWork::class);
    }

    public function save(HomeWork $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HomeWork $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HomeWork[] Returns an array of HomeWork objects
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

    /**
     * @return HomeWork[] Returns an array of HomeWorkStudentReply objects
     */
    public function findByTeacher($teacher): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.course', 'c', 'WITH', 'h.course = c.id')
//            ->join('c.teacher', 't', 'WITH', 'c.teacher = t.id')
            ->andWhere('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?HomeWork
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
