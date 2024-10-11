<?php

namespace App\Repository\School\Study\Teacher;

use App\Entity\School\Schooling\Registration\Student;
use App\Entity\School\Study\Teacher\HomeWorkStudentReply;
use App\Entity\School\Study\Teacher\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeWorkStudentReply>
 *
 * @method HomeWorkStudentReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeWorkStudentReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeWorkStudentReply[]    findAll()
 * @method HomeWorkStudentReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeWorkStudentReplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeWorkStudentReply::class);
    }

    public function save(HomeWorkStudentReply $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HomeWorkStudentReply $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HomeWorkStudentReply[] Returns an array of HomeWorkStudentReply objects
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
//

/**
     * @return HomeWorkStudentReply[] Returns an array of HomeWorkStudentReply objects
     */
    public function findByStudent(Student $student): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.homeWorkRegistration', 'homeWorkRegistration')
            ->join('homeWorkRegistration.student', 'studentRegistration')
            ->join('studentRegistration.student', 'student')
            ->andWhere('student = :student')
            ->setParameter('student', $student)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return HomeWorkStudentReply[] Returns an array of HomeWorkStudentReply objects
     */
    public function findByTeacher(Teacher $teacher): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.homeWorkRegistration', 'homeWorkRegistration')
            ->join('homeWorkRegistration.homeWork', 'homeWork')
            ->join('homeWork.course', 'teacherCourseRegistration')
            ->join('teacherCourseRegistration.teacher', 'teacher')
            ->andWhere('teacher = :teacher')
            ->setParameter('teacher', $teacher)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?HomeWorkStudentReply
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
