<?php

namespace App\Repository\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\SuspensionHour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuspensionHour>
 *
 * @method SuspensionHour|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuspensionHour|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuspensionHour[]    findAll()
 * @method SuspensionHour[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuspensionHourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuspensionHour::class);
    }

    public function save(SuspensionHour $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SuspensionHour $entity, bool $flush = false): void
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
