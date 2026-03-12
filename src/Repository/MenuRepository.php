<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findByFilters(
    ?float $prixMin,
    ?float $prixMax,
    ?string $theme,
    ?string $regime,
    ?int $personnesMin
): array {
    $qb = $this->createQueryBuilder('m')
        ->leftJoin('m.theme', 't')
        ->leftJoin('m.regime', 'r')
        ->addSelect('t', 'r')
        ->orderBy('m.titre', 'ASC');

    if ($prixMin !== null) {
        $qb->andWhere('m.prixParPersonne >= :prixMin')
           ->setParameter('prixMin', $prixMin);
    }

    if ($prixMax !== null) {
        $qb->andWhere('m.prixParPersonne <= :prixMax')
           ->setParameter('prixMax', $prixMax);
    }

    if ($theme !== null && $theme !== '') {
        $qb->andWhere('t.libelle = :theme')
           ->setParameter('theme', $theme);
    }

    if ($regime !== null && $regime !== '') {
        $qb->andWhere('r.libelle = :regime')
           ->setParameter('regime', $regime);
    }

    if ($personnesMin !== null) {
        $qb->andWhere('m.nombrePersonneMinimum >= :personnesMin')
           ->setParameter('personnesMin', $personnesMin);
    }

    return $qb->getQuery()->getResult();
}
}