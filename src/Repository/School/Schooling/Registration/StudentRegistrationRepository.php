<?php

namespace App\Repository\School\Schooling\Registration;

use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentRegistration>
 *
 * @method StudentRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentRegistration[]    findAll()
 * @method StudentRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentRegistration::class);
    }

    public function save(StudentRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return StudentRegistration[] Returns an array of StudentRegistration objects
     */
    public function findByStudentMatricule(Institution $institution, string $field, mixed $studentMatricule = null): array
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('(s.'.$field.')');

        if ($studentMatricule !== null)
            $queryBuilder = $queryBuilder
                ->innerJoin('s.student','stud',Expr\Join::WITH,'stud.matricule = :studentMatricule')
                ->setParameter('studentMatricule', $studentMatricule);
        else
            $queryBuilder = $queryBuilder->distinct();

        $queryBuilder->where('s.institution = :institution')->setParameter('institution',$institution);

        return $queryBuilder->getQuery()
            ->getResult()
            ;
    }


    public function findOneByStudentMatricule(int $studId,SchoolClass $class,Year $year,School $school): ?StudentRegistration
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.student','stud')
            ->where('stud.id = :studentId')
            ->andWhere('s.currentClass = :class')
            ->andWhere('s.currentYear = :year')
            ->andWhere('s.school = :school')
            ->setParameters(new ArrayCollection(
                    array(
                        new Parameter('studentId', $studId),
                        new Parameter('class', $class),
                        new Parameter('year', $year),
                        new Parameter('school', $school)
                    ))
            )
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @return StudentRegistration[] Returns an array of StudentRegistration objects
     */
    public function findClasses(Institution $institution): array
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->innerJoin('s.currentclasse','class')
//            ->innerJoin('s.year','year')
//            ->innerJoin('s.institution','institution')
//            ->innerJoin('class.school','school')
//            ->select(['(s.institution)','(s.year)','(class.school)','(s.class)'])
            ->select(['(s.currentyear)','(class.school)','(class)'])
            ->where('s.institution = :institution')
//            ->andWhere('s.exampleField = :val')
            ->distinct()
            ->setParameter('institution', $institution)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult()
            ;
    }

    /**
     * @return StudentRegistration[] Returns an array of StudentRegistration objects
     */
    public function findStudents(Institution $institution): array
    {
        return $this->createQueryBuilder('s')

            ->andWhere('s.institution = :institution')
            ->setParameter('institution', $institution)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return StudentRegistration[] Returns an array of StudentRegistration objects
//     */
//    public function findByClassAndYear($classe,$year): array
//    {
//        return $this->createQueryBuilder('s')
//            ->join('s.student', 'stud', Expr\Join::WITH, 'stud.year = :year')
//            ->where( 's.classe = :classe')
//            ->setParameters(new ArrayCollection(array(
//                new Parameter('year', $year),
//                new Parameter('classe', $classe)
//            )))
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    /**
//     * @return StudentRegistration[] Returns an array of StudentRegistration objects
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

//    public function findOneBySomeField($value): ?StudentRegistration
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }



/*public function findByExampleField()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.studentRegistration IS NOT NULL')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }*/

    /**
     * @return StudentRegistration[] Returns an array of StudentRegistration objects
     */
    public function findOldStudentRegistration($currentYear, $userInstitution): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.currentYear = :year')
            ->andWhere('s.institution = :institution')
            ->andWhere('s.studentRegistration IS NOT NULL')
            ->setParameter('year', $currentYear)
            ->setParameter('institution', $userInstitution)
            ->orderBy('s.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return StudentRegistration[] Returns an array of StudentRegistration objects
     */
    public function findOldStudentRegistrationSchool($currentYear, $userInstitution, $school): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.currentYear = :year')
            ->andWhere('s.institution = :institution')
            ->andWhere('s.school = :school')
            ->andWhere('s.studentRegistration IS NOT NULL')
            ->setParameter('year', $currentYear)
            ->setParameter('institution', $userInstitution)
            ->setParameter('school', $school)
            ->orderBy('s.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return StudentRegistration[] Returns an array of StudentRegistration objects
     */
    public function findNewStudentRegistration($currentYear, $userInstitution): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.currentYear = :year')
            ->andWhere('s.institution = :institution')
            ->andWhere('s.studentRegistration IS NULL')
            ->setParameter('year', $currentYear)
            ->setParameter('institution', $userInstitution)
            ->orderBy('s.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return int Returns the count of registered StudentRegistration objects for the current year
     */
    public function countRegisteredStudentsForCurrentYear($currentYear): int
    {

        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.status = :status')
            ->andWhere('s.currentYear = :currentYear')
            ->setParameter('status', 'registered')
            ->setParameter('currentYear', $currentYear)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int Returns the count of dismissed StudentRegistration objects for the current year
     */
    public function countDismissedStudentsForCurrentYear($currentYear): int
    {

        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.status = :status')
            ->andWhere('s.currentYear = :currentYear')
            ->setParameter('status', 'dismissed')
            ->setParameter('currentYear', $currentYear)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int Returns the count of resigned StudentRegistration objects for the current year
     */
    public function countResignedStudentsForCurrentYear($currentYear): int
    {

        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.status = :status')
            ->andWhere('s.currentYear = :currentYear')
            ->setParameter('status', 'resigned')
            ->setParameter('currentYear', $currentYear)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return StudentRegistration[] Returns an array of Customer objects
     */
    public function findStudentRegistrationByClass($value): array
    {
        return $this->createQueryBuilder('c')
            //->innerJoin('c.studentRegistration', 's', 'WITH', 'c.studentRegistration = s.id')
            ->andWhere('c.studentRegistration IS NULL')
            ->andWhere('c.classe = :val')
            ->andWhere('c.status = :state')
            ->setParameter('val', $value)
            ->setParameter('state', 'registered')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }


//    /**
//     * @return array Returns the last 5 StudentRegistration objects
//     */
//    public function getLastFiveRegistrations(): array
//    {
//        return $this->createQueryBuilder('s')
//            ->orderBy('s.id', 'DESC')
//            ->setMaxResults(5)
//            ->getQuery()
//            ->getResult();
//    }


}
