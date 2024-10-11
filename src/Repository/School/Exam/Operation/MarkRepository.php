<?php

namespace App\Repository\School\Exam\Operation;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\Security\Institution\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mark>
 *
 * @method Mark|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mark|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mark[]    findAll()
 * @method Mark[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mark::class);
    }

    public function save(Mark $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mark $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Mark[] Returns an array of Mark objects
     */
    public function findSchoolMarks(Institution $institution, mixed $studentMatricule = null): array
    {

        $queryBuilder = $this->createQueryBuilder('s')
//            ->select('s')
            ->innerJoin('s.studCourseReg', 'studCourseReg')
            ->innerJoin('studCourseReg.StudRegistration', 'StudRegistration')
            ->innerJoin('StudRegistration.student', 'student');
        return ($studentMatricule !== null ? $queryBuilder
            ->where('student.matricule = :studentMatricule')
            ->setParameter('studentMatricule', $studentMatricule) : $queryBuilder)
            ->andWhere('s.institution = :institution')
            ->setParameter('institution', $institution)

            //->where('s.sequence = :classe')
            //->setParameter('classe', 'sequence')
            //->addSelect('s.class')
            /*->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->distinct()
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)*/

//            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Mark[] Returns an array of Mark objects
     */
    public function findClassProgramIds(SchoolClass $class, ?EvaluationPeriod $evaluationPeriod,Sequence $sequence,NoteType $noteType): array
    {

        $queryBuilder = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.classProgram) as classProgramId')
            ->where('s.class = :class')
        ->andWhere('s.sequence = :sequence')
        ->andWhere('s.noteType = :noteType');

        if ($evaluationPeriod) {
            $queryBuilder = $queryBuilder->andWhere('s.evaluationPeriod = :evaluationPeriod')->setParameter('evaluationPeriod', $evaluationPeriod);
        }

        return $queryBuilder
            ->setParameter('class', $class)
            ->setParameter('sequence', $sequence)
            ->setParameter('noteType', $noteType)
            ->distinct()

        //->where('s.sequence = :classe')
        //->setParameter('classe', 'sequence')
        //->addSelect('s.class')
        /*->andWhere('s.exampleField = :val')
        ->setParameter('val', $value)
        ->distinct()
        ->orderBy('s.id', 'ASC')
        ->setMaxResults(10)*/

//            ->distinct()
        ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Mark[] Returns an array of Mark objects
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

//    public function findOneBySomeField($value): ?Mark
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
