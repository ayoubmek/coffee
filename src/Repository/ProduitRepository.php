<?php
namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Produits d'une catégorie, triés par id croissant
     * @return Produit[]
     */
    public function findAllGroupedByCategory(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.categorie', 'c')
            ->orderBy('c.id', 'ASC')   // category order
            ->addOrderBy('p.id', 'ASC') // product order (Espresso first)
            ->getQuery()
            ->getResult();
    }
}