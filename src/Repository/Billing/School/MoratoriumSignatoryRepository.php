<?php

namespace App\Repository\Billing\School;

use App\Entity\Billing\School\MoratoriumSignatory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoratoriumSignatory>
 *
 * @method MoratoriumSignatory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoratoriumSignatory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoratoriumSignatory[]    findAll()
 * @method MoratoriumSignatory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoratoriumSignatoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoratoriumSignatory::class);
    }

    public function save(MoratoriumSignatory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MoratoriumSignatory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MoratoriumSignatory[] Returns an array of MoratoriumSignatory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MoratoriumSignatory
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
