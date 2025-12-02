<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $product->setUpdatedAt(new \DateTimeImmutable());
        $em->persist($product);

        if ($flush) {
            $em->flush();
        }
    }

    public function remove(Product $product, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($product);

        if ($flush) {
            $em->flush();
        }
    }
}
