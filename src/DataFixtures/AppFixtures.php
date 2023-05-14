<?php

namespace App\DataFixtures;

use App\Entity\Emprunteur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create();

        for ($i=0; $i < 10; $i++) { 
            $emprunteur = new Emprunteur();

            $emprunteur->setNom($faker->firstName);
            $emprunteur->setPrenom($faker->lastName);
            $emprunteur->setAdresse($faker->adresse);
            $emprunteur->setContact($faker->contact);
            $manager->persist($emprunteur);
        }

        $manager->flush();   
    }
}
