<?php

namespace App\Repository\Treasury;

use App\Entity\Treasury\CashDeskOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CashDeskOperation>
 *
 * @method CashDeskOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CashDeskOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CashDeskOperation[]    findAll()
 * @method CashDeskOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CashDeskOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashDeskOperation::class);
    }

    public function save(CashDeskOperation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CashDeskOperation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CashDeskOperation[] Returns an array of CashDeskOperation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CashDeskOperation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
