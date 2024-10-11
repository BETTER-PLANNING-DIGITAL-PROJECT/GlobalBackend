<?php

namespace App\Repository\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\AbsencePermit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbsencePermit>
 *
 * @method AbsencePermit|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbsencePermit|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbsencePermit[]    findAll()
 * @method AbsencePermit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbsencePermitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbsencePermit::class);
    }

    public function save(AbsencePermit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AbsencePermit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ModuleCategory[] Returns an array of ModuleCategory objects
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

//    public function findOneBySomeField($value): ?ModuleCategory
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
