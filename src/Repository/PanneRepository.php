<?php

namespace App\Repository;

use App\Entity\Panne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class PanneRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Panne::class);
    }

    public function getCarWithLastPanne($id)
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->leftJoin('p.cars','car' )
            ->addSelect('car')
            ->andWhere('car.id = :id')
            ->setParameter('id', $id)
        ;

        $qb->setMaxResults(2);
        return $qb
            ->getQuery()
            ->getResult()
            ;

    }

    
    /*public function findByImmat($immat)
    {
        return $this->createQueryBuilder('p')
            ->where('p.something = :value')->setParameter('value', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
