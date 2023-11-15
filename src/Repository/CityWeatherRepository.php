<?php

namespace App\Repository;

use App\Entity\CityWeather;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CityWeather>
 *
 * @method CityWeather|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityWeather|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityWeather[]    findAll()
 * @method CityWeather[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityWeatherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CityWeather::class);
    }

//    /**
//     * @return CityWeather[] Returns an array of CityWeather objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findTemperature()
    {
        return $this->createQueryBuilder('c')
            ->where('c.alerttemperature IS NOT NULL')
            ->andWhere('c.alerttemperature != :emptyString')
            ->setParameter('emptyString', '')
            ->getQuery()
            ->getResult();
    }
}
