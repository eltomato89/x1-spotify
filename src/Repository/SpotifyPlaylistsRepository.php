<?php

namespace App\Repository;

use App\Entity\SpotifyPlaylists;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SpotifyPlaylists|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpotifyPlaylists|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpotifyPlaylists[]    findAll()
 * @method SpotifyPlaylists[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpotifyPlaylistsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotifyPlaylists::class);
    }

    // /**
    //  * @return SpotifyPlaylists[] Returns an array of SpotifyPlaylists objects
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
    public function findOneBySomeField($value): ?SpotifyPlaylists
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
