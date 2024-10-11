<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\FormulaTh;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormulaTh>
 *
 * @method FormulaTh|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormulaTh|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormulaTh[]    findAll()
 * @method FormulaTh[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormulaThRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormulaTh::class);
    }

    public function save(FormulaTh $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FormulaTh $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FormulaTh[] Returns an array of FormulaTh objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormulaTh
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
