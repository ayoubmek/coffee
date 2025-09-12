<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        // Récupération de toutes les catégories depuis la base
        $categories = $categorieRepository->findAll();

        // Envoi des données à la vue Twig
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
