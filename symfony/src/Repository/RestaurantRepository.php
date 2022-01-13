<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function findByUser(User $user, $skip = null, $fetch = null)
    {
        if(isset($skip, $fetch) && intval($skip) >= 0 && intval($fetch) > 0){
            return $this->createQueryBuilder('r')
                ->join('r.responsable', 'u')
                ->where('u.id = :userId')->setParameter('userId', $user->getId())
                ->orderBy('r.id', 'DESC')
                ->setMaxResults($fetch)
                ->setFirstResult($skip)
                ->getQuery()
                ->getResult()
            ;
        }
        return $this->createQueryBuilder('r')
            ->join('r.responsable', 'u')
            ->where('u.id = :userId')->setParameter('userId', $user->getId())
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Restaurant[] Returns an array of Restaurant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Restaurant
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
