<?php

namespace App\Repository;

use App\Entity\Receta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Receta>
 *
 * @method Receta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Receta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Receta[]    findAll()
 * @method Receta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Receta::class);
    }

    public function add(Receta $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        /*foreach ($entity->getIngredientes() as $ingrediente) {
            $ingrediente->addReceta($entity);
            $this->getEntityManager()->persist($ingrediente);
        }*/
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Receta $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Receta[] Returns an array of Receta objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Receta
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
