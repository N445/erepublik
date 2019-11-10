<?php

namespace App\Repository\KillsStats;

use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Plane|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plane|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plane[]    findAll()
 * @method Plane[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plane::class);
    }

    /**
     * @param Profile   $profile
     * @param \DateTime $date
     * @return Plane|null
     * @throws NonUniqueResultException
     */
    public function getPlaneByDate(Profile $profile, \DateTime $date)
    {
        return $this->createQueryBuilder('p')
                    ->where('p.profile = :profile')
                    ->andWhere('p.date BETWEEN :from AND :to')
                    ->setParameter('profile', $profile)
                    ->setParameter('from', $date->format('Y-m-d 00:00:00'))
                    ->setParameter('to', $date->format('Y-m-d 23:59:59'))
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Plane[] Returns an array of Plane objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Plane
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
