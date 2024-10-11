<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EvaluationPeriod>
 *
 * @method EvaluationPeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationPeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationPeriod[]    findAll()
 * @method EvaluationPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationPeriod::class);
    }

    public function save(EvaluationPeriod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EvaluationPeriod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

        /**
     * @return EvaluationPeriod[] Returns an array of EvaluationPeriod objects
     */
    public function findPreviousEvaluationPeriod(EvaluationPeriod $evaluationPeriod): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.number < :number')
//            ->andWhere('e.name < :name')
            ->setParameter('number', $evaluationPeriod->getNumber())
//            ->setParameter('name', $evaluationPeriod->getName())
            ->orderBy('e.number', 'ASC')
//            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return EvaluationPeriod[] Returns an array of EvaluationPeriod objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EvaluationPeriod
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
