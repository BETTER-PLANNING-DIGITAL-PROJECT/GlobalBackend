<?php

namespace App\Repository\Setting\Finance;

use App\Entity\Setting\Finance\ExpenseHeading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExpenseHeading>
 *
 * @method ExpenseHeading|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseHeading|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseHeading[]    findAll()
 * @method ExpenseHeading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseHeadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseHeading::class);
    }

    public function save(ExpenseHeading $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExpenseHeading $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ExpenseHeading[] Returns an array of ExpenseHeading objects
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

//    public function findOneBySomeField($value): ?ExpenseHeading
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
