<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }


// src/Repository/LocationRepository.php

/**
 * @return Location[]
 */
public function findByUser(User $user): array
{
    return $this->createQueryBuilderByUser($user)
        ->getQuery()
        ->getResult();
}

public function createQueryBuilderByUser(User $user): QueryBuilder
{
    return $this->createQueryBuilder('l')
        ->where('l.user = :user')
        ->setParameter('user', $user)
        ->orderBy('l.address', 'ASC');
}

public function createQueryBuilderByUserOrNull(User $user): QueryBuilder
{
    return $this->createQueryBuilder('l')
        ->where('l.user = :user')
        ->orWhere('l.user is null')
        ->setParameter('user', $user)
        ->orderBy('l.address', 'ASC');
}


    //    /**
    //     * @return Location[] Returns an array of Location objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Location
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
