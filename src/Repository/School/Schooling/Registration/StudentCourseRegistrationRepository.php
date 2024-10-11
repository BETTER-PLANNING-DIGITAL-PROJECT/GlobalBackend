<?php

namespace App\Repository\School\Schooling\Registration;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Schooling\Registration\Student;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentCourseRegistration>
 *
 * @method StudentCourseRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentCourseRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentCourseRegistration[]    findAll()
 * @method StudentCourseRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentCourseRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentCourseRegistration::class);
    }

    public function save(StudentCourseRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentCourseRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return StudentCourseRegistration[] Returns an array of StudentCourseRegistration objects
     */
    public function findByStudentMatricule(Institution $institution, mixed $studentMatricule = null): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($studentMatricule !== null)
            $queryBuilder = $queryBuilder->innerJoin('s.StudRegistration','studReg')
                ->innerJoin('studReg.student','stud',Expr\Join::WITH,'stud.matricule = :studentMatricule')
            ->setParameter('studentMatricule', $studentMatricule);

        $queryBuilder->andWhere('s.institution = :institution');
        $queryBuilder->setParameter('institution',$institution);

        return $queryBuilder->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return StudentCourseRegistration[] Returns an array of StudCourseReg objects
     */
    public function findByClassForAttendance($class, $currentYear): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT(s.StudRegistration), t.name, t.firstName, t.matricule, t.id as studentId')
            ->innerJoin(StudentRegistration::class, 'r', 'WITH', 'r.id = s.StudRegistration')
            ->innerJoin(Student::class, 't', 'WITH', 'r.student = t.id')
            ->andWhere('s.class = :val')
            ->andWhere('s.year = :res')
            ->andWhere('r.currentYear = :currentyear')
            ->setParameter('val', $class)
            ->setParameter('res', $currentYear)
            ->setParameter('currentyear', $currentYear)
            //->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return StudentCourseRegistration[] Returns an array of StudCourseReg objects
     */
    public function findByStudentId(Institution $institution, mixed $id = null): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($id !== null)
            $queryBuilder = $queryBuilder->innerJoin('s.StudRegistration','studReg')
                ->innerJoin('studReg.student','stud',Expr\Join::WITH,'stud.id = :studentId')
                ->setParameter('studentId', $id);

        $queryBuilder->andWhere('s.institution = :institution');
        $queryBuilder->setParameter('institution',$institution);

        return $queryBuilder->getQuery()
            ->getResult()
            ;
    }

    // Recuperer les inscriptions aux matieres des autres periodes d'evaluations
    /**
     * @return StudentCourseRegistration[] Returns an array of StudentCourseRegistration objects
     */
    public function findByStudentOtherEvaluationPeriodRegistration(StudentCourseRegistration $studentCourseRegistration,EvaluationPeriod $evaluationPeriod): ?StudentCourseRegistration
    {
        $student = $studentCourseRegistration->getStudRegistration();
        $classProgram = $studentCourseRegistration->getClassProgram();
        $codeuvc = $classProgram->getCodeuvc();
        $nameuvc = $classProgram->getNameuvc();

        try {
            $result = $this->createQueryBuilder('s')
                ->innerJoin('s.StudRegistration', 'student')
                ->innerJoin('s.classProgram', 'classProgram')
                ->andWhere('student = :student')
                ->andWhere('classProgram.codeuvc = :codeuvc')
                ->andWhere('classProgram.nameuvc = :nameuvc')
                ->andWhere('s.evaluationPeriod = :evaluationPeriod')
                ->setParameter('student', $student)
                ->setParameter('codeuvc', $codeuvc)
                ->setParameter('nameuvc', $nameuvc)
                ->setParameter('evaluationPeriod', $evaluationPeriod)
                ->getQuery()
                ->getOneOrNullResult();

            return $result;
        }
        catch (NonUniqueResultException $nonUniqueResultException){
            return null;
        }
    }

    // Recuperer l'inscription a la matiere a partir d'un programme de classe pour un etudiant
    /**
     * @return StudentCourseRegistration[] Returns an array of StudentCourseRegistration objects
     */
    public function findByStudentAndClassProgram(StudentRegistration $student,ClassProgram $classProgram,EvaluationPeriod $evaluationPeriod): ?StudentCourseRegistration
    {
        $codeuvc = $classProgram->getCodeuvc();
        $nameuvc = $classProgram->getNameuvc();

        try {
            $result = $this->createQueryBuilder('s')
                ->innerJoin('s.StudRegistration', 'student')
                ->innerJoin('s.classProgram', 'classProgram')
                ->andWhere('student = :student')
                ->andWhere('classProgram.codeuvc = :codeuvc')
                ->andWhere('classProgram.nameuvc = :nameuvc')
                ->andWhere('s.evaluationPeriod = :evaluationPeriod')
                ->setParameter('student', $student)
                ->setParameter('codeuvc', $codeuvc)
                ->setParameter('nameuvc', $nameuvc)
                ->setParameter('evaluationPeriod', $evaluationPeriod)
                ->getQuery()
                ->getOneOrNullResult();

            return $result;
        }
        catch (NonUniqueResultException $nonUniqueResultException){
            return null;
        }
    }

//    /**
//     * @return StudentCourseRegistration[] Returns an array of StudentCourseRegistration objects
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

//    public function findOneBySomeField($value): ?StudentCourseRegistration
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
