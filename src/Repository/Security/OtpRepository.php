<?php

namespace App\Repository\Security;

use App\Entity\Security\Otp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Otp>
 *
 * @method Otp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Otp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Otp[]    findAll()
 * @method Otp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Otp::class);
    }

    public function save(Otp $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Otp $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Otp[] Returns an array of Otp objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Otp
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function countOldRejected(): int
    {
        return $this->getOldRejectedQueryBuilder()->select('COUNT(o.id)')->getQuery()->getSingleScalarResult();
    }

    public function deleteOldRejected(): int
    {
        return $this->getOldRejectedQueryBuilder()->delete()->getQuery()->execute();
    }

    private function getOldRejectedQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.transport = :state_sms or o.used = :state_false')
            ->andWhere('o.expireAt < :date')
            ->setParameters([
                'state_sms' => 'sms',
                'state_false' => false,
                'date' => new \DateTime('now'),
            ])
            ;
    }
}
