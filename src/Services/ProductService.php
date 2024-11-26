<?php

namespace App\Services;

use App\Entity\Product;

class ProductService
{
    /**
     * Product List array compiler function.
     *
     * @param Product[] $products
     * @return array
     */
    public function productArrayCompiler(array $products): array
    {
        $productList = [];
        foreach ($products as $product) {
            $productList[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'quantity' => $product->getQuantity(),
                'price' => $product->getPrice(),
                'isActive' => $product->isActive(),
            ];
        }
        return $productList;
    }
}