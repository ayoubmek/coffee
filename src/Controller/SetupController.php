<?php

// src/Controller/SetupController.php
namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetupController
{
    #[Route('/setup', name: 'setup')]
    public function setup(EntityManagerInterface $em): Response
    {
        $rows = [
            [1, 'Hot Drinks', 'assets/media/svg/food-icons/coffee.jpg'],
            [2, 'Cold Coffee', 'assets/media/svg/food-icons/Thé.jpg'],
            [3, 'Milkshakes & Smoothies', 'assets/media/svg/food-icons/Chocolatchaud.jpg'],
            [4, 'Dessert', 'assets/media/svg/food-icons/Dessert.jpg'],
            [6, 'Mojitos & Matcha', 'assets/media/svg/food-icons/Jus.jpg'],
            [7, 'Special Cocktails', 'assets/media/ch/SpecialCocktails.webp'],
            [9, 'Extras & Soft Drinks', 'assets/media/ch/Extras&SoftDrinks.webp'],
        ];

        foreach ($rows as [$id, $nom, $icone]) {
            $cat = new Categorie();
            $cat->setId($id)        // only if your entity has setId (or remove and use SERIAL)
                 ->setNom($nom)
                 ->setIcone($icone);
            $em->persist($cat);
        }
        $em->flush();

        return new Response('Categories inserted');
    }
}