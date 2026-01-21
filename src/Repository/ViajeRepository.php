<?php

namespace App\Repository;

use App\Entity\Viaje;
use App\Enum\ViajeEstado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Viaje>
 */
class ViajeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Viaje::class);
    }


    /**
     * @return Viaje[] Devuelve los viajes posteriores a la fecha/hora actual
     */
    public function findProximosViajes(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.fechaHora > :now')
            ->andWhere('v.estado != :cancelado')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('cancelado', ViajeEstado::CANCELADO)            
            ->orderBy('v.fechaHora', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Viaje[] Returns an array of Viaje objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Viaje
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
