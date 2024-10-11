<?php

namespace App\Repository\Partner;

use App\Entity\Partner\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function save(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Customer[] Returns an array of Customer objects
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

//    public function findOneBySomeField($value): ?Customer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function findCustomerWhereStudentRegistrationByClass($value): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.studentRegistration', 's', 'WITH', 'c.studentRegistration = s.id')
            ->andWhere('c.studentRegistration IS NOT NULL')
            ->andWhere('s.classe = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function findCustomerWhereStudentRegistration(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.studentRegistration', 's', 'WITH', 'c.studentRegistration = s.id')
            ->andWhere('c.studentRegistration IS NOT NULL')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
