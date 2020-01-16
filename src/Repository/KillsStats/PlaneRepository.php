<?php

namespace App\Repository\KillsStats;

use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

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
                    ->andWhere('p.dateId = :date')
                    ->setParameter('profile', $profile)
                    ->setParameter('date', sprintf('%s-%s', $date->format('Y'), $date->format('W')))
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    /**
     * @return Plane|null
     * @throws \Exception
     */
    public function getLastOrNextPlanes($isLast = true)
    {
        if ($isLast) {
            $mondayString = 'previous monday';
        } else {
            $mondayString = 'next monday';
        }
        $monday = (new \DateTime())->setTimestamp(strtotime($mondayString, (new \DateTime("NOW"))->getTimestamp()));
        return $this->getBaseQuery()
                    ->andWhere('p.date BETWEEN :from AND :to')
                    ->setParameter('from', $monday->format('Y-m-d 00:00:00'))
                    ->setParameter('to', $monday->format('Y-m-d 23:59:59'))
                    ->getQuery()
                    ->getResult()
            ;
    }


    public function getPlanesStats()
    {
        return $this->createQueryBuilder('p')
                    ->addSelect('profile', 'unitemilitaire')
                    ->leftJoin('p.profile', 'profile')
                    ->leftJoin('profile.unitemilitaire', 'unitemilitaire')
                    ->orderBy('p.date', 'DESC')
                    ->addOrderBy('unitemilitaire.name')
                    ->addOrderBy('profile.name')
                    ->addOrderBy('p.kills')
                    ->getQuery()
                    ->getResult()
            ;
    }


    /**
     * @return QueryBuilder
     */
    public function getBaseQuery()
    {
        return $this->createQueryBuilder('p')
                    ->addSelect('profile', 'unitemilitaire')
                    ->leftJoin('p.profile', 'profile')
                    ->leftJoin('profile.unitemilitaire', 'unitemilitaire')
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
