<?php

namespace App\Repository\Profile;

use App\Entity\Profile\UniteMilitaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method UniteMilitaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method UniteMilitaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method UniteMilitaire[]    findAll()
 * @method UniteMilitaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UniteMilitaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UniteMilitaire::class);
    }

    /**
     * @param int $identifier
     * @return UniteMilitaire|null
     * @throws NonUniqueResultException
     */
    public function getUnitemilitaireByIdentifier(int $identifier)
    {
        return $this->createQueryBuilder('u')
                    ->where('u.identifier = :identifier')
                    ->setParameter('identifier', $identifier)
                    ->getQuery()
                    ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return UniteMilitaire[] Returns an array of UniteMilitaire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UniteMilitaire
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
