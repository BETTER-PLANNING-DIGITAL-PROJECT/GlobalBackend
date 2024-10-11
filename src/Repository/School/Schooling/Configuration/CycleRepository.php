<?php

namespace App\Repository\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Cycle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cycle>
 *
 * @method Cycle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cycle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cycle[]    findAll()
 * @method Cycle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CycleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cycle::class);
    }

    public function save(Cycle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cycle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Cycle[] Returns an array of Cycle objects
     */
    public function findByInstitution($institution): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.institution = :institution')
            ->setParameter('institution', $institution)
            ->orderBy('r.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Cycle[] Returns an array of Cycle objects
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

//    public function findOneBySomeField($value): ?Cycle
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
