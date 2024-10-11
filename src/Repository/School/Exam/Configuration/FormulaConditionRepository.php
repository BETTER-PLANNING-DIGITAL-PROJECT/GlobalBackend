<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\FormulaCondition;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\School\Schooling\Configuration\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormulaCondition>
 *
 * @method FormulaCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormulaCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormulaCondition[]    findAll()
 * @method FormulaCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormulaConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormulaCondition::class);
    }

        public function isSecondPrincipal($fieldsArray): array
    {
        $formulaCondition = $this->findOneBy($fieldsArray);
        extract($fieldsArray);
        return ($isMain === true && isset($formulaCondition)) ? [$formulaCondition] : [];
    }

    /**
     * @return FormulaCondition[] Returns an array of FormulaCondition objects
     */
    public function getPreviousLevelsFormula(School $school,Level $level): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.level','level')
            ->andWhere('f.school = :school')
            ->andWhere('f.isMain = false')
            ->andWhere('level.number < :levelNumber')
            ->setParameter('school', $school)
            ->setParameter('levelNumber', $level->getNumber())
            ->orderBy('level.number', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return FormulaCondition[] Returns an array of FormulaCondition objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormulaCondition
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
