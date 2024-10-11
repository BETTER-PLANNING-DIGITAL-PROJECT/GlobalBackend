<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\TeacherCourseRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeacherCourseRegistration>
 *
 * @method TeacherCourseRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeacherCourseRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeacherCourseRegistration[]    findAll()
 * @method TeacherCourseRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeacherCourseRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeacherCourseRegistration::class);
    }

    public function save(TeacherCourseRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TeacherCourseRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return TeacherCourseRegistration[] Returns an array of Campus objects
     */
    public function findByInstitutionYear($institution, $year): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.institution = :institution')
            ->andWhere('t.year = :year')
            ->setParameter('institution', $institution)
            ->setParameter('year', $year)
            ->orderBy('t.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return TeacherCourseRegistration[] Returns an array of TeacherCourseRegistration objects
     */
    public function findByCurrentTeacher($teacher): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.teacher', 'teacher')
            ->andWhere('teacher = :teacher')
            ->setParameter('teacher', $teacher)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return TeacherCourseRegistration[] Returns an array of TeacherCourseRegistration objects
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

//    public function findOneBySomeField($value): ?TeacherCourseRegistration
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    public function countTeacherCourses($value)
   {
       return $this->createQueryBuilder('t')
           ->select('count(t.course)')
            ->andWhere('t.teacher = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
       ;
    }

    public function countTeacherCoursesInprogress($value)
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.course)')
            ->andWhere('t.teacher = :val')
            ->andWhere('t.hourlyRateExhausted > 0')
            ->andWhere('t.hourlyRateExhausted < t.hourlyRateVolume')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    public function countTeacherCoursesCompleted($value)
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.course)')
            ->andWhere('t.teacher = :val')
            ->andWhere('t.hourlyRateVolume = t.hourlyRateExhausted')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
