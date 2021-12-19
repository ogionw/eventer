<?php

namespace App\Warehouse\Domain\Repository;

use App\Warehouse\Domain\Product\Product;

interface ProductEventRepositoryInterface
{
    public function getProduct(string $sku): Product;
    public function save(Product $product) : void;
}