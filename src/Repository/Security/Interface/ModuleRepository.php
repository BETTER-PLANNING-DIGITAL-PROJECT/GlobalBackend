<?php

namespace App\Repository\Security\Interface;

use App\Entity\Security\Interface\Menu;
use App\Entity\Security\Interface\Module;
use App\Entity\Security\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Module>
 *
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    public function save(Module $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Module $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Module[] Returns an array of Module objects
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

//    public function findOneBySomeField($value): ?Module
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByMenu(): array
    {
        return $this->createQueryBuilder('m')
            ->join(Menu::class, 'e', 'WITH', 'e.module = m.id')
            ->andWhere('e.module IS NOT NULL')
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Module[] Returns an array of Module objects
     */
    public function findOneByProfile($profile): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.name, m.id')
            ->innerJoin(Role::class, 'r', 'WITH', 'r.module = m.id')
            ->andWhere('r.profile = :val')
            ->setParameter('val', $profile)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
