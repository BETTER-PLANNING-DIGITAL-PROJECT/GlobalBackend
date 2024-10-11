<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Schooling\Configuration\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkGrade>
 *
 * @method MarkGrade|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkGrade|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkGrade[]    findAll()
 * @method MarkGrade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkGradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkGrade::class);
    }


    /**
     * @throws NonUniqueResultException
     */
    public function getBySchoolForMark(School $school, float $mark)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.school = :school')
            ->andWhere('m.min <= :mark AND m.max > :mark')

            ->setParameter('school', $school)
            ->setParameter('mark', $mark)

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


//    /**
//     * @return MarkGrade[] Returns an array of MarkGrade objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MarkGrade
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
