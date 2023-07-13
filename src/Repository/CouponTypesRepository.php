<?php

namespace App\Repository;

use App\Entity\CouponTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CouponTypes>
 *
 * @method CouponTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method CouponTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method CouponTypes[]    findAll()
 * @method CouponTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponTypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CouponTypes::class);
    }

//    /**
//     * @return CouponTypes[] Returns an array of CouponTypes objects
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

//    public function findOneBySomeField($value): ?CouponTypes
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
