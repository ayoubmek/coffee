<?php
namespace App\Controller;
use App\Entity\Payment; 
use App\Entity\Produit; 

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;

use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\HistoryRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{

      #[Route('/service', name: 'app_service')]
    public function index1(CategorieRepository $categorieRepository,ProduitRepository $produitRepository): Response
    { 
        $categories = $categorieRepository->findAll();
        $produits = $prodRepo->findAllGroupedByCategory();

        return $this->render('service/service.html.twig', [
            'categories' => $categories,
            'produits' => $produits,

        ]);
    }

    #[Route('/produit/{id}', name: 'app_produit_details')]
public function details(Produit $produit): Response
{
    return $this->render('produit/details.html.twig', [
        'produit' => $produit,
    ]);
}


}