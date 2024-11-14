<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function Zenstruck\Foundry\faker;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $rowCount = 1000;
        for ($i = 0; $i < $rowCount; $i++) {

            $product = new Product();

            $productName = faker()->text(50);
            $product->setName($productName);

            $product->setSlug((new AsciiSlugger())->slug($productName)->lower());

            $product->setDescription(faker()->text());
            $product->setQuantity(faker()->numberBetween(1, 100));
            $product->setPrice(faker()->numberBetween(49, 999));
            $product->setActive(true);
            $product->setCreatedAt(new \DateTime());
            $product->setUpdatedAt(new \DateTime());

            $manager->persist($product);
            $manager->flush();
        }
    }

}
