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

        $productsData = [
            [
                'name' => "Kit d'hygiène recyclable",
                'short' => 'Pour une salle de bain éco-friendly',
                'full' => "Ce kit d'hygiène recyclable rassemble l'essentiel pour une salle de bain plus responsable. 
Il contient des accessoires durables, pensés pour limiter les déchets du quotidien sans compromis sur le confort d'utilisation. 
Parfait pour débuter une démarche zéro déchet et adopter de nouveaux gestes durables.",
                'price' => 24.99,
                'picture' => 'kit_hygiene_recyclable.webp',
            ],
            [
                'name' => 'Shot tropical',
                'short' => 'Fruits frais, pressés à froid',
                'full' => "Un concentré de saveurs en un seul shot. 
Préparé à base de fruits frais pressés à froid, ce shot tropical apporte une touche de fraîcheur et d'énergie à tout moment de la journée. 
À déguster bien frais pour profiter pleinement de ses arômes.",
                'price' => 4.50,
                'picture' => 'shot_tropical.webp',
            ],
            [
                'name' => 'Gourde en bois',
                'short' => "50cl, bois d'olivier",
                'full' => "Cette gourde en bois d'olivier allie esthétisme et durabilité. 
Sa contenance de 50cl en fait la compagne idéale pour vos déplacements quotidiens. 
Le bois issu de forêts gérées durablement offre une finition unique à chaque pièce.",
                'price' => 16.90,
                'picture' => 'gourde_bois.webp',
            ],
            [
                'name' => 'Disques Démaquillants x3',
                'short' => 'Solution efficace pour vous démaquiller en douceur',
                'full' => "Ces disques démaquillants lavables remplacent les cotons jetables traditionnels. 
Utilisables avec votre démaquillant habituel, ils nettoient la peau en douceur tout en réduisant vos déchets. 
Après utilisation, il vous suffit de les passer en machine pour les réutiliser.",
                'price' => 19.90,
                'picture' => 'disques_demaquillants.webp',
            ],
            [
                'name' => 'Bougie Lavande & Patchouli',
                'short' => 'Cire naturelle',
                'full' => "Une bougie parfumée à la lavande et au patchouli pour une ambiance chaleureuse et apaisante. 
Coulée à partir de cire naturelle, elle diffuse une lumière douce et un parfum subtil, idéal pour vos moments de détente.",
                'price' => 32.00,
                'picture' => 'bougie_lavande_patchouli.webp',
            ],
            [
                'name' => 'Brosse à dent',
                'short' => 'Bois de hêtre rouge issu de forêts gérées durablement',
                'full' => "Cette brosse à dent en bois de hêtre rouge est une alternative durable au plastique. 
Son manche ergonomique assure une bonne prise en main, tandis que ses poils souples permettent un brossage efficace et tout en douceur.",
                'price' => 5.40,
                'picture' => 'brosse_a_dent.webp',
            ],
            [
                'name' => 'Kit de couverts en bois',
                'short' => 'Revêtement Bio en olivier & sac de transport',
                'full' => "Le kit de couverts en bois comprend tout le nécessaire pour vos repas nomades. 
Son revêtement bio et son sac de transport réutilisable en font un compagnon idéal pour les pique-niques, le bureau ou les voyages.",
                'price' => 12.30,
                'picture' => 'kit_couvert_bois.webp',
            ],
            [
                'name' => 'Nécessaire, déodorant Bio',
                'short' => '50ml déodorant à l’eucalyptus',
                'full' => "Ce déodorant bio à l'eucalyptus offre une sensation de fraîcheur durable tout au long de la journée. 
Sa formule est conçue pour respecter la peau tout en limitant les odeurs, sans sels d'aluminium ni ingrédients controversés.",
                'price' => 8.50,
                'picture' => 'deodorant_bio.webp',
            ],
            [
                'name' => 'Savon Bio',
                'short' => 'Thé, Orange & Girofle',
                'full' => "Un savon bio aux notes de thé, d'orange et de girofle pour un moment de bien-être au quotidien. 
Sa mousse onctueuse nettoie la peau en douceur et laisse un parfum chaud et épicé après le rinçage.",
                'price' => 18.90,
                'picture' => 'savon_bio.webp',
            ],
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setShortDescription($data['short']);
            $product->setFullDescription($data['full']); // description longue définie
            $product->setPrice($data['price']);
            $product->setPicture($data['picture']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
