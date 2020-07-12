<?php

namespace App\Repository;

use App\Entity\SpotifyCredentials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SpotifyCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpotifyCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpotifyCredentials[]    findAll()
 * @method SpotifyCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpotifyCredentialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotifyCredentials::class);
    }

    // /**
    //  * @return SpotifyCredentials[] Returns an array of SpotifyCredentials objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SpotifyCredentials
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
