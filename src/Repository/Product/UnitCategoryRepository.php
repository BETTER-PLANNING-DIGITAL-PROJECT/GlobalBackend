<?php

namespace App\Repository\Product;

use App\Entity\Product\UnitCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnitCategory>
 *
 * @method UnitCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnitCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnitCategory[]    findAll()
 * @method UnitCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnitCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnitCategory::class);
    }

    public function save(UnitCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UnitCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return UnitCategory[] Returns an array of UnitCategory objects
     */
    public function findByInstitution($institution): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.institution = :institution')
            ->setParameter('institution', $institution)
            ->orderBy('c.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return UnitCategory[] Returns an array of UnitCategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UnitCategory
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
