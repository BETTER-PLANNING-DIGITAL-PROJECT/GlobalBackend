<?php

namespace App\Repository\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Guardianship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guardianship>
 *
 * @method Guardianship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Guardianship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Guardianship[]    findAll()
 * @method Guardianship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuardianshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guardianship::class);
    }

    public function save(Guardianship $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Guardianship $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Guardianship[] Returns an array of Guardianship objects
     */
    public function findByInstitution($institution): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.institution = :institution')
            ->setParameter('institution', $institution)
            ->orderBy('g.id', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Guardianship[] Returns an array of Guardianship objects
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

//    public function findOneBySomeField($value): ?Guardianship
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
