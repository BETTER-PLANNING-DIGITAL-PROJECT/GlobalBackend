<?php

namespace App\Repository\Security\Interface;

use App\Entity\Security\Interface\Permission;
use App\Entity\Security\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Permission>
 *
 * @method Permission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permission[]    findAll()
 * @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function save(Permission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Permission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Permission[] Returns an array of Permission objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Permission
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findBySomeField($profile, $menu): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p.name, p.id, r.status')
            ->innerJoin(Role::class, 'r', 'WITH', 'r.privilege = p.id')
            ->andWhere('r.profile = :profile')
            ->andWhere('r.menu = :menu')
            ->setParameter('profile', $profile)
            ->setParameter('menu', $menu)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findSearchExistPermission($profile, $menu, $permission): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT(p.name) as name, p.id')
            ->innerJoin(Role::class, 'r', 'WITH', 'r.privilege = p.id')
            ->andWhere('r.profile = :profile')
            ->andWhere('r.menu = :menu')
            ->andWhere('r.privilege = :perms')
            ->setParameter('profile', $profile)
            ->setParameter('menu', $menu)
            ->setParameter('perms', $permission)
            ->getQuery()
            ->getResult()
            ;
    }
}
