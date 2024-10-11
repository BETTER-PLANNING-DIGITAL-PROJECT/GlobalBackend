<?php

namespace App\Repository\Security\Interface;

use App\Entity\Security\Interface\Menu;
use App\Entity\Security\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function save(Menu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Menu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Menu[] Returns an array of Menu objects
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

//    public function findOneBySomeField($value): ?Menu
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findParentMenu(): ?array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :val')
            ->andWhere('m.parent IS NULL')
            ->setParameter('val', 'sub')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $module
     * @param $position
     * @return ?Menu
     * @throws NonUniqueResultException
     */
    public function findMenuByModulePositionType($module, $position, $type): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.module = :val')
            ->andWhere('m.position = :res')
            ->andWhere('m.title = :type')
            ->setParameter('val', $module)
            ->setParameter('res', $position)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /**
     * @param $module
     * @param $position
     * @return ?Menu
     * @throws NonUniqueResultException
     */
    public function findByModulePosition($module, $position): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.module = :val')
            ->andWhere('m.position = :res')
            //->andWhere('m.parent IS NULL')
            ->setParameter('val', $module)
            ->setParameter('res', $position)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $module
     * @param $positionSubmenu
     * @return ?Menu
     * @throws NonUniqueResultException
     */
    public function findByModulePositionMenu($module, $positionSubmenu, $menuParent): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.module = :val')
            ->andWhere('m.position = :res')
            ->andWhere('m.parent IS NOT NULL')
            ->andWhere('m.parent = :parent')
            ->setParameter('val', $module)
            ->setParameter('res', $positionSubmenu)
            ->setParameter('parent', $menuParent)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @return Menu[]
     */
    public function findByModule($module): ?array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.module = :mod')
            ->setParameter('mod', $module)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Menu[]
     */
    public function findByModuleAndProfile($module, $profile): ?array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin(Role::class, 'r', 'WITH', 'r.menu = m.id')
            ->andWhere('m.module = :mod')
            ->andWhere('r.profile = :val')
            ->setParameter('mod', $module)
            ->setParameter('val', $profile)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Menu[]
     */
    public function findByModuleMenuWherePermissions($module, $children): ?array
    {
        return $this->createQueryBuilder('m')
            //->innerJoin('m.permissions', 'p', 'WITH', 'p.id IS NOT NULL')
            ->andWhere('m.module = :res')
            ->andWhere('m.parent = :child')
            ->setParameter('res', $module)
            ->setParameter('child', $children)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Menu[]
     */
    public function findByModuleProfileParent($profile, $module, $parent): ?array
    {
        return $this->createQueryBuilder('m')
            //->innerJoin('m.permissions', 'p', 'WITH', 'p.id IS NOT NULL')
            ->innerJoin(Role::class, 'r', 'WITH', 'r.menu = m.id')
            ->andWhere('r.profile = :profile')
            ->andWhere('m.module = :mod')
            ->andWhere('m.parent = :parent')
            ->setParameter('profile', $profile)
            ->setParameter('mod', $module)
            ->setParameter('parent', $parent)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Menu[]
     */
    public function findByModuleParent($module, $parent): ?array
    {
        return $this->createQueryBuilder('m')
            //->innerJoin('m.permissions', 'p', 'WITH', 'p.id IS NOT NULL')
            ->andWhere('m.module = :mod')
            ->andWhere('m.parent = :parent')
            ->setParameter('mod', $module)
            ->setParameter('parent', $parent)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

}
