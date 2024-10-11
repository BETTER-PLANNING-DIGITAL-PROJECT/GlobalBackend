<?php

namespace App\Repository\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Fee;
use App\Entity\School\Schooling\Configuration\FeeInstallment;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\Security\Session\Year;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeeInstallment>
 *
 * @method FeeInstallment|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeeInstallment|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeeInstallment[]    findAll()
 * @method FeeInstallment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeeInstallmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeeInstallment::class);
    }

    public function save(FeeInstallment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FeeInstallment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return FeeInstallment[] Returns an array of FeeInstallment objects
     */
    public function findByFee(Year $year, School $school, SchoolClass $class): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.fee', 'fee')  // Join FeeInstallment with Fee entity
            ->andWhere('fee.school = :school')  // Filter by school on Fee entity
            ->andWhere('fee.year = :year')  // Filter by year on Fee entity
            ->andWhere('fee.class = :class')  // Filter by class on Fee entity
            ->setParameter('school', $school)
            ->setParameter('year', $year)
            ->setParameter('class', $class)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();
    }


//    public function findOneBySomeField($value): ?FeeInstallment
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
