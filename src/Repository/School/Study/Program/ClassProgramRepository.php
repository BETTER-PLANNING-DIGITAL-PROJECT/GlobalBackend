<?php

namespace App\Repository\School\Study\Program;

use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassProgram>
 *
 * @method ClassProgram|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassProgram|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassProgram[]    findAll()
 * @method ClassProgram[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassProgram::class);
    }

    public function save(ClassProgram $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClassProgram $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ClassProgram[] Returns an array of ClassProgram objects
     */
    public function findByStudentMatricule(Institution $institution, mixed $studentMatricule = null): array
    {
        $queryBuilder = $this->createQueryBuilder('c');

        return ($studentMatricule === null ? $queryBuilder : $queryBuilder
            ->innerJoin('App\Entity\School\Schooling\Registration\StudentRegistration', 'sr')
            ->innerJoin('sr.student', 'student')
            ->where('sr.school = c.school')
            ->andWhere('sr.currentyear = c.year')
            ->andWhere('sr.currentclasse = c.class')

            ->andWhere('c.institution = :institution')
            ->andWhere('student.matricule = :studentMatricule')
            ->setParameter('studentMatricule', $studentMatricule)
        )
            ->andWhere('c.institution = :institution')
            ->setParameter('institution', $institution)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ClassProgram[] Returns an array of ClassProgram objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }



    /**
     * @return int Returns the count of courses objects
     */
    public function countCourses(): int
    {

        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    public function findOneBySomeField($value): ?ClassProgram
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
