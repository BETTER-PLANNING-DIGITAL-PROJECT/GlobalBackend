<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\Sequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sequence>
 *
 * @method Sequence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sequence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sequence[]    findAll()
 * @method Sequence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sequence::class);
    }

    public function save(Sequence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sequence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Sequence[] Returns an array of Sequence objects
     */
    public function findPreviousSequences(Sequence $sequence): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.number < :number')
//            ->andWhere('e.name < :name')
            ->setParameter('number', $sequence->getNumber())
//            ->setParameter('name', $evaluationPeriod->getName())
            ->orderBy('s.number', 'ASC')
//            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?Sequence
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
