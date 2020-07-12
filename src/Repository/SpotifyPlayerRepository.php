<?php

namespace App\Repository;

use App\Entity\SpotifyPlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SpotifyPlayer|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpotifyPlayer|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpotifyPlayer[]    findAll()
 * @method SpotifyPlayer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpotifyPlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotifyPlayer::class);
    }

    // /**
    //  * @return SpotifyPlayer[] Returns an array of SpotifyPlayer objects
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
    public function findOneBySomeField($value): ?SpotifyPlayer
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
