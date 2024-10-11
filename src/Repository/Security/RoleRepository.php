<?php

namespace App\Repository\Security;

use App\Entity\Security\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function save(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Role[] Returns an array of Role objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Role
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    /**
     * @return Role[] Returns an array of Role objects
     */
    public function findDistinctModuleByProfile($profile): array
    {
        return $this->createQueryBuilder('r')
            ->select('m.name, m.color, m.icon, m.position, m.path, m.layout, MIN(m.id) as id')
            ->innerJoin('r.module', 'm', 'WITH', 'm.id = r.module')
            ->andWhere('r.profile = :profile')
            ->andWhere('r.status = :enable')
            ->groupBy('m.name, m.color, m.icon, m.position, m.path, m.layout')
            ->setParameter('profile', $profile)
            ->setParameter('enable', true)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Role[] Returns an array of Role objects
     */
    public function findDistinctMenuByProfile($profile, $moduleId): array
    {
        return $this->createQueryBuilder('r')
            ->select('m.name, m.title, m.path, m.icon, m.type, m.position, m.active, MIN(m.id) as id')
            ->innerJoin('r.menu', 'm', 'WITH', 'm.id = r.menu')
            ->andWhere('r.profile = :profile')
            ->andWhere('r.module = :moduleId')
            ->andWhere('m.parent IS NULL')
            // ->andWhere('r.status = true')
            ->groupBy('m.name, m.title, m.path, m.icon, m.type, m.position, m.active')
            ->setParameter('profile', $profile)
            ->setParameter('moduleId', $moduleId)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Role[] Returns an array of Role objects
     */
    public function findDistinctChildrenMenuByRoleName($moduleId, $menuName, $profile): array
    {
        return $this->createQueryBuilder('r')
            ->select('DISTINCT m.name, m.title, m.type, m.path, m.position, MIN(m.id) as id')
            ->innerJoin('r.menu', 'm', 'WITH', 'm.id = r.menu')
            ->innerJoin('m.parent', 'c', 'WITH', 'c.id = m.parent')
            ->andWhere('r.profile = :profile')
            ->andWhere('r.module = :moduleId')
            ->andWhere('c.name = :menuName')
            ->andWhere('m.parent IS NOT NULL')
            ->andWhere('r.status = true')
            //->groupBy('c.name, c.type, c.icon')
            ->groupBy('m.name, m.title, m.path, m.type, m.position')
            ->setParameter('menuName', $menuName)
            ->setParameter('moduleId', $moduleId)
            ->setParameter('profile', $profile)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

}
