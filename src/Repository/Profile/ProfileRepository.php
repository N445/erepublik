<?php

namespace App\Repository\Profile;

use App\Entity\Profile\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profile[]    findAll()
 * @method Profile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /**
     * @param int $identifier
     * @return Profile|null
     * @throws NonUniqueResultException
     */
    public function getProfileByIdentifier(int $identifier)
    {
        return $this->createQueryBuilder('p')
                    ->where('p.identifier = :identifier')
                    ->setParameter('identifier', $identifier)
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    /**
     * @param int $identifier
     * @return Profile|null
     * @throws NonUniqueResultException
     */
    public function getProfilesAdmin()
    {
        return $this->createQueryBuilder('p')
                    ->leftJoin('p.unitemilitaire', 'u')
                    ->orderBy('u.name', 'ASC')
                    ->addOrderBy('p.name', 'ASC')
                    ->getQuery()
                    ->getResult()
            ;
    }

    // /**
    //  * @return Profile[] Returns an array of Profile objects
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
    public function findOneBySomeField($value): ?Profile
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
