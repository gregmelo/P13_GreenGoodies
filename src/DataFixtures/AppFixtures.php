<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

         $faker = Factory::create('fr_FR');

        $product1 = new Product();
        $product1->setName('Kit d\'hygiène recyclable');
        $product1->setShortDescription('Pour une salle de bain éco-friendly');
        $product1->setFullDescription($faker->paragraphs(3, true));
        $product1->setPrice(24.99);
        $product1->setPicture('kit_hygiene_recyclable.webp');
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setName('Shot Tropical');
        $product2->setShortDescription('Fruits frais, pressés à froid');
        $product2->setFullDescription($faker->paragraphs(3, true));
        $product2->setPrice(4.50);
        $product2->setPicture('shot_tropical.webp');
        $manager->persist($product2);

        $product3 = new Product();
        $product3->setName('Gourde en bois');
        $product3->setShortDescription('50cl, bois d\'olivier');
        $product3->setFullDescription($faker->paragraphs(3, true));
        $product3->setPrice(16.90);
        $product3->setPicture('gourde_bois.webp');
        $manager->persist($product3);

        $manager->flush();
    }
}
