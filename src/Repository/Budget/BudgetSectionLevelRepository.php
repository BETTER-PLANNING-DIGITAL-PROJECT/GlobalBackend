<?php

namespace App\Repository\Budget;

use App\Entity\Budget\BudgetSectionLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetSectionLevel>
 *
 * @method BudgetSectionLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetSectionLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetSectionLevel[]    findAll()
 * @method BudgetSectionLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetSectionLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetSectionLevel::class);
    }

    public function save(BudgetSectionLevel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BudgetSectionLevel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return BudgetSectionLevel[] Returns an array of BudgetSectionLevel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BudgetSectionLevel
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
