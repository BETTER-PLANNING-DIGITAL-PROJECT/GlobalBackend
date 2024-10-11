<?php

namespace App\Repository\School\Exam\Configuration;

use App\Entity\School\Exam\Configuration\ExamInstitutionSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExamInstitutionSettings>
 *
 * @method ExamInstitutionSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExamInstitutionSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExamInstitutionSettings[]    findAll()
 * @method ExamInstitutionSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExamInstitutionSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExamInstitutionSettings::class);
    }

    public function save(ExamInstitutionSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExamInstitutionSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ExamInstitutionSettings[] Returns an array of ExamInstitutionSettings objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ExamInstitutionSettings
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
